<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\LinkStat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $profileIds = $user->networkProfiles()->pluck('id');
        $linkIds = $user->networkProfiles()->pluck('link_id')->filter()->unique();

        $links = Link::whereIn('id', $linkIds)->get();
        $profiles = $user->networkProfiles()->with('link')->get();

        $recentStats = LinkStat::whereIn('link_id', $linkIds)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($stat) {
                return [
                    'id' => $stat->id,
                    'log' => $stat->log,
                    'created_at' => $stat->created_at,
                    'link_name' => optional($stat->link)->name ?? 'N/A',
                ];
            });

        return Inertia::render('User/Dashboard', [
            'links' => $links,
            'profiles' => $profiles,
            'recentStats' => $recentStats,
        ]);
    }

    public function dailySummary(Request $request)
    {
        $user = $request->user();

        // Get all user profiles with stats and their links
        $profiles = $user->networkProfiles()->with(['stats', 'link'])->get();

        $dailyStats = [];
        $linkLogsByDate = [];

        // Step 1: Collect stats grouped by date from all profiles
        foreach ($profiles as $profile) {
            $dateStats = $profile->stats ?? [];
            foreach ($dateStats as $stat) {
                $date = $stat->date;

                if (!isset($dailyStats[$date])) {
                    $dailyStats[$date] = [
                        'date' => $date,
                        'opening_balance' => 0,
                        'topup_today' => 0,
                        'closing_balance' => 0,
                        'total_logs' => 0,
                    ];
                }

                $dailyStats[$date]['opening_balance'] += $stat->opening_balance;
                $dailyStats[$date]['topup_today'] += $stat->topup_today;
                $dailyStats[$date]['closing_balance'] += $stat->closing_balance ?? $stat->current_balance;
            }
        }

        // Step 2: Get unique links and determine base_logs_type
        $uniqueLinks = $profiles
            ->filter(fn($p) => $p->link)
            ->pluck('link')
            ->unique('id');

        foreach ($uniqueLinks as $link) {
            $logStats = \DB::table('link_stats')
                ->selectRaw('DATE(created_at) as date, MIN(created_at) as min_time, MAX(created_at) as max_time')
                ->where('link_id', $link->id)
                ->groupByRaw('DATE(created_at)')
                ->get();

            foreach ($logStats as $stat) {
                $date = $stat->date;
                $baseColumn = $link->base_logs_type ?? 'log';

                $first = \DB::table('link_stats')
                    ->where('link_id', $link->id)
                    ->whereDate('created_at', $date)
                    ->where('created_at', $stat->min_time)
                    ->value($baseColumn);

                $last = \DB::table('link_stats')
                    ->where('link_id', $link->id)
                    ->whereDate('created_at', $date)
                    ->where('created_at', $stat->max_time)
                    ->value($baseColumn);

                if (!isset($linkLogsByDate[$date])) {
                    $linkLogsByDate[$date] = 0;
                }

                if (!is_null($first) && !is_null($last)) {
                    $linkLogsByDate[$date] += ($last - $first);
                }
            }
        }

        // Step 3: Merge log counts into dailyStats
        foreach ($linkLogsByDate as $date => $logCount) {
            if (!isset($dailyStats[$date])) {
                $dailyStats[$date] = [
                    'date' => $date,
                    'opening_balance' => 0,
                    'topup_today' => 0,
                    'closing_balance' => 0,
                    'total_logs' => 0,
                ];
            }

            $dailyStats[$date]['total_logs'] = $logCount;
        }

        // Step 4: Sort
        $sorted = collect($dailyStats)->sortByDesc('date')->values();

        return Inertia::render('User/DailySummary', [
            'summary' => $sorted,
        ]);
    }

    public function leaveImpersonation()
    {
        if (session()->has('impersonate')) {
            $adminId = session()->pull('impersonate');
            Auth::loginUsingId($adminId);
            session()->forget('impersonate');

            return redirect()->route('admin.users.index')->with('success', 'Impersonation ended.');
        }

        return redirect()->route('admin.users.index');
    }
}
