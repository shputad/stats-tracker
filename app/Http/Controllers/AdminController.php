<?php

namespace App\Http\Controllers;

use App\Models\NetworkChannel;
use App\Models\Link;
use App\Models\NetworkProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'channelsCount' => NetworkChannel::count(),
            'linksCount' => Link::count(),
            'profilesCount' => NetworkProfile::count(),
            'usersCount' => User::count(),
            'recentLinks' => Link::latest()->take(5)->get(),
            'recentProfiles' => NetworkProfile::with('networkChannel', 'link')->latest()->take(5)->get(),
        ]);
    }

    public function dailySummary(Request $request)
    {
        $user = $request->user();
        $profiles = $user->networkProfiles()->with(['user', 'networkChannel', 'stats', 'link'])->get();

        $dailyStats = [];
        $dailyProfiles = [];

        foreach ($profiles as $profile) {
            foreach ($profile->stats as $stat) {
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

                $dailyProfiles[$date][] = [
                    'profile_id' => $profile->id,
                    'channel' => $profile->networkChannel->name,
                    'account_id' => $profile->account_id,
                    'opening_balance' => $stat->opening_balance,
                    'topup_today' => $stat->topup_today,
                    'closing_balance' => $stat->closing_balance ?? $stat->current_balance,
                ];
            }
        }

        // Logs from links
        $uniqueLinks = $profiles->pluck('link')->filter()->unique('id');

        $linkLogsByDate = [];

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
                    $linkLogsByDate[$date] = ($linkLogsByDate[$date] ?? 0) + ($last - $first);
                }
            }
        }

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

        $summary = collect($dailyStats)->sortByDesc('date')->values()->all();

        return Inertia::render('Admin/DailySummary', [
            'summary' => $summary,
            'profilesByDate' => $dailyProfiles,
        ]);
    }
}
