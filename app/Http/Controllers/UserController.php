<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\LinkStat;
use App\Models\NetworkProfile;
use App\Models\User;
use App\Models\Setting;
use App\Models\DailyProfitOverride;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
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

        return Inertia::render('User/DailySummary', [
            'summary' => $summary,
            'profilesByDate' => $dailyProfiles,
        ]);
    }

    public function dailyProfit(Request $request)
    {
        $user = $request->user();
        $profitPercentage = (float) ($user->profit_percentage ?? 0);

        $profiles = $user->networkProfiles()->with(['link', 'stats'])->get();

        $daily = [];

        foreach ($profiles as $profile) {
            $link = $profile->link;
            if (!$link) continue;

            $linkId = $link->id;
            $linkName = $link->name ?? null;
            $baseColumn = $link->base_logs_type ?? 'log';

            foreach ($profile->stats as $stat) {
                $date = $stat->date;
                $userSpending = (float) $stat->opening_balance + (float) $stat->topup_today - (float) ($stat->closing_balance ?? $stat->current_balance);

                // Initialize the date row
                if (!isset($daily[$date])) {
                    $daily[$date] = [
                        'date' => $date,
                        'spending' => 0,
                        'cr' => 0,
                        'links' => [],
                    ];
                }

                $daily[$date]['spending'] += $userSpending;

                // Global logs for this link & date
                $firstLog = \DB::table('link_stats')
                    ->whereDate('created_at', $date)
                    ->where('link_id', $linkId)
                    ->orderBy('created_at')
                    ->value($baseColumn);

                $lastLog = \DB::table('link_stats')
                    ->whereDate('created_at', $date)
                    ->where('link_id', $linkId)
                    ->orderByDesc('created_at')
                    ->value($baseColumn);

                $logs = (!is_null($firstLog) && !is_null($lastLog)) ? ($lastLog - $firstLog) : 0;

                if (!isset($daily[$date]['total_logs'])) {
                    $daily[$date]['total_logs'] = $logs;
                }

                if (!isset($daily[$date]['links'][$linkId])) {
                    $daily[$date]['links'][$linkId] = [
                        'link_id' => $linkId,
                        'name' => $linkName,
                        'spending' => $userSpending,
                        'logs' => $logs,
                    ];
                } else {
                    $daily[$date]['links'][$linkId]['spending'] += $userSpending;
                }

                // Total spending across ALL users for this link and date
                $totalLinkSpending = \DB::table('network_profile_stats')
                    ->join('network_profiles', 'network_profiles.id', '=', 'network_profile_stats.profile_id')
                    ->where('network_profiles.link_id', $linkId)
                    ->where('network_profile_stats.date', $date)
                    ->selectRaw('SUM(opening_balance + topup_today - COALESCE(closing_balance, current_balance)) as total_spending')
                    ->value('total_spending') ?? 0;

                // CR override
                $overrideCr = DailyProfitOverride::where('link_id', $linkId)
                    ->where('date', $date)
                    ->value('override_cr');

                $dynamic_cr = $totalLinkSpending > 0 ? round($logs / $totalLinkSpending, 4) : 0;

                $daily[$date]['links'][$linkId]['dynamic_cr'] = $dynamic_cr;

                $cr = $overrideCr ?? $dynamic_cr;

                $daily[$date]['links'][$linkId]['cr'] = $cr;

                if ($overrideCr) {
                    $daily[$date]['links'][$linkId]['override_cr'] = $overrideCr;
                }
            }
        }

        // Weighted average CR per day
        foreach ($daily as &$row) {
            $linkSpendings = array_column($row['links'], 'spending');
            $sumSpending = array_sum($linkSpendings);

            $weightedCrSum = 0;
            foreach ($row['links'] as $link) {
                $weightedCrSum += $link['cr'] * $link['spending'];
            }

            $row['cr'] = $sumSpending > 0 ? round($weightedCrSum / $sumSpending, 4) : 0;
        }

        $updateInterval = Setting::where('key', 'profile_stats_update_interval')->value('value') ?? 10;

        return Inertia::render('User/DailyProfit', [
            'summary' => collect($daily)->sortByDesc('date')->values()->all(),
            'profitPercentage' => $profitPercentage,
            'settings' => ['profile_stats_update_interval' => $updateInterval]
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
