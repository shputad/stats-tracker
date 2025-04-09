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
        $user = auth()->user()->load('link');

        $profileIds = $user->networkProfiles()->pluck('id');
        $linkIds = [$user->link_id];

        $links = Link::whereIn('id', $linkIds)->get();
        $profiles = $user->networkProfiles()->get();

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
        $user = $request->user()->load('link');

        $profiles = $user->networkProfiles()->with(['user', 'networkChannel', 'stats', 'user.link', 'snapshots' => function ($q) {
            $q->latest();
        }])->get();
    
        $dailyStats = [];
        $dailyProfiles = [];
    
        foreach ($profiles as $profile) {
            $snapshots = $profile->snapshots->sortByDesc('taken_at')->values();
            $latestSnapshot = $snapshots->get(0);
            $previousSnapshot = $snapshots->get(1);
            $interval = $latestSnapshot && $previousSnapshot
                ? Carbon::parse($latestSnapshot->taken_at)->diffForHumans($previousSnapshot->taken_at, [
                    'parts' => 1, 'join' => true, 'syntax' => Carbon::DIFF_ABSOLUTE
                ])
                : null;
            
            $lastSpending = $previousSnapshot && $latestSnapshot
                ? max(0, $previousSnapshot->balance - $latestSnapshot->balance)
                : 0;
    
            foreach ($profile->stats as $stat) {
                $date = $stat->date;
                $spending = (float) $stat->opening_balance + (float) $stat->topup_today - (float) ($stat->closing_balance ?? $stat->current_balance);
    
                if (!isset($dailyStats[$date])) {
                    $dailyStats[$date] = [
                        'date' => $date,
                        'opening_balance' => 0,
                        'topup_today' => 0,
                        'closing_balance' => 0,
                        'total_logs' => 0,
                        'last_spending' => 0,
                        'oldest_update_ago' => null,
                        'spending_interval' => null,
                    ];
                }

                $dailyStats[$date]['opening_balance'] += $stat->opening_balance;
                $dailyStats[$date]['topup_today'] += $stat->topup_today;
                $dailyStats[$date]['closing_balance'] += $stat->closing_balance ?? $stat->current_balance;

                if (!isset($dailyStats[$date]['oldest_update_ago'])) {
                    $dailyStats[$date]['oldest_update_ago'] = $latestSnapshot?->taken_at;
                } else if (strtotime($dailyStats[$date]['oldest_update_ago']) > strtotime($latestSnapshot?->taken_at)) {
                    $dailyStats[$date]['oldest_update_ago'] = $latestSnapshot?->taken_at;
                }

                if (isset($dailyStats[$date]['oldest_update_ago']) && $dailyStats[$date]['oldest_update_ago']) {
                    $dailyStats[$date]['oldest_update_ago'] = Carbon::parse($dailyStats[$date]['oldest_update_ago'])->diffForHumans();
                }

                $dailyStats[$date]['last_spending'] += $lastSpending;
                $dailyStats[$date]['spending_interval'] = $interval;

                $dailyProfiles[$date][] = [
                    'profile_id' => $profile->id,
                    'channel' => $profile->networkChannel->name,
                    'account_id' => $profile->account_id,
                    'opening_balance' => $stat->opening_balance,
                    'topup_today' => $stat->topup_today,
                    'closing_balance' => $stat->closing_balance ?? $stat->current_balance,
                    'last_update_at' => Carbon::parse($latestSnapshot?->taken_at)?->diffForHumans(),
                    'last_spending' => $lastSpending,
                    'spending_interval' => $interval,
                ];
            }
        }
    
        $uniqueLinks = collect([$user->link])->filter()->unique('id');
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
                    'last_spending' => 0,
                    'oldest_update_ago' => null,
                    'spending_interval' => null,
                ];
            }
    
            $dailyStats[$date]['total_logs'] = $logCount;
        }
    
        $summary = collect($dailyStats)->sortByDesc('date')->values()->all();
        $updateInterval = Setting::where('key', 'profile_stats_update_interval')->value('value') ?? 10;
    
        return Inertia::render('User/DailySummary', [
            'summary' => $summary,
            'profilesByDate' => $dailyProfiles,
            'settings' => ['profile_stats_update_interval' => $updateInterval]
        ]);
    }

    public function dailyProfit(Request $request)
    {
        $user = $request->user()->load('link');
        $updateInterval = Setting::where('key', 'profile_stats_update_interval')->value('value') ?? 10;
        $profitPercentage = (float) ($user->profit_percentage ?? 0);
    
        $profiles = $user->networkProfiles()->with(['user.link', 'stats', 'user.linkAssignments'])->get();
    
        $daily = [];
        $spendingByProfile = [];
        $logCache = [];
    
        foreach ($profiles as $profile) {
            foreach ($profile->stats as $profileStat) {
                $date = $profileStat->date;
                $spending = (float) $profileStat->opening_balance + (float) $profileStat->topup_today - (float) ($profileStat->closing_balance ?? $profileStat->current_balance);
    
                if (!isset($daily[$date])) {
                    $daily[$date] = [
                        'date' => $date,
                        'spending' => 0,
                        'total_logs' => 0,
                        'cr' => 0,
                        'last_spending' => 0,
                        'last_logs' => 0,
                        'spending_interval' => '',
                        'logs_interval' => '',
                        'oldest_snapshot_time' => '',
                        'latest_log_time' => '',
                        'links' => [],
                    ];
                }
    
                $daily[$date]['spending'] += $spending;
    
                // Collect assignment windows
                $assignments = $profile->user->linkAssignments->filter(function ($a) use ($date) {
                    $start = Carbon::parse($a->assigned_at)->startOfDay();
                    $end = $a->unassigned_at ? Carbon::parse($a->unassigned_at)->endOfDay() : now()->endOfDay();
                    $check = Carbon::parse($date)->startOfDay();
                    return $check->between($start, $end);
                });
    
                foreach ($assignments as $assignment) {
                    $linkId = $assignment->link_id;
                    $link = Link::find($linkId);
                    if (!$link) continue;
    
                    $baseColumn = $link->base_logs_type ?? 'log';
    
                    $start = Carbon::parse($assignment->assigned_at)->greaterThan(Carbon::parse($date)->startOfDay())
                        ? Carbon::parse($assignment->assigned_at)
                        : Carbon::parse($date)->startOfDay();
    
                    $end = $assignment->unassigned_at
                        ? (Carbon::parse($assignment->unassigned_at)->lessThan(Carbon::parse($date)->endOfDay())
                            ? Carbon::parse($assignment->unassigned_at)
                            : Carbon::parse($date)->endOfDay())
                        : Carbon::parse($date)->endOfDay();
    
                    $logCache[$date][$linkId][] = [$start, $end];
    
                    // Prepare link row (spending + cr etc)
                    if (!isset($daily[$date]['links'][$linkId])) {
                        $spendingByProfile[$date][$profile->id] = $spending;
    
                        $daily[$date]['links'][$linkId] = [
                            'link_id' => $linkId,
                            'name' => $link->name,
                            'spending' => $spending,
                            'logs' => 0,
                            'cr' => 0,
                            'dynamic_cr' => 0,
                        ];
                    } else {
                        if (!isset($spendingByProfile[$date][$profile->id])) {
                            $spendingByProfile[$date][$profile->id] = $spending;
                            $daily[$date]['links'][$linkId]['spending'] += $spending;
                        }
                    }

                    $logSnapshots = \DB::table('link_stats')
                        ->where('link_id', $linkId)
                        ->whereDate('created_at', $date)
                        ->orderByDesc('created_at')
                        ->take(2)
                        ->get();

                    $logLatest = $logSnapshots->get(0);
                    $logPrevious = $logSnapshots->get(1);

                    $logsInterval = null;
                    $logsDiff = null;

                    if ($logLatest && $logPrevious) {
                        $logsInterval = Carbon::parse($logLatest->created_at)
                            ->diffForHumans(Carbon::parse($logPrevious->created_at), [
                                'parts' => 1,
                                'syntax' => Carbon::DIFF_ABSOLUTE,
                            ]);

                        $logsDiff = max(0, $logLatest->$baseColumn - $logPrevious->$baseColumn);
                    }

                    $spendingSnapshots = $profile->snapshots->sortByDesc('taken_at')->values();
                    $lastSnap = $spendingSnapshots->get(0);
                    $prevSnap = $spendingSnapshots->get(1);

                    $spendingInterval = null;
                    $spendingDiff = null;

                    if ($lastSnap && $prevSnap) {
                        $spendingInterval = Carbon::parse($lastSnap->taken_at)
                            ->diffForHumans(Carbon::parse($prevSnap->taken_at), [
                                'parts' => 1,
                                'syntax' => Carbon::DIFF_ABSOLUTE,
                            ]);

                        $spendingDiff = max(0, $prevSnap->balance - $lastSnap->balance);
                    }

                    if (isset($daily[$date]['last_spending'])) {
                        $daily[$date]['last_spending'] += $spendingDiff;
                    } else {
                        $daily[$date]['last_spending'] = $spendingDiff;
                    }

                    $daily[$date]['spending_interval'] = $spendingInterval;
                    $daily[$date]['last_logs'] = $logsDiff;
                    $daily[$date]['logs_interval'] = $logsInterval;

                    // Set oldest snapshot (for spending)
                    if (!isset($daily[$date]['oldest_snapshot_time']) || !strtotime($daily[$date]['oldest_snapshot_time']) || strtotime($daily[$date]['oldest_snapshot_time']) > strtotime($lastSnap?->taken_at)) {
                        $daily[$date]['oldest_snapshot_time'] = $lastSnap?->taken_at;
                    }

                    if (!isset($daily[$date]['latest_log_time']) || !strtotime($daily[$date]['latest_log_time']) || strtotime($logLatest?->created_at) > strtotime($daily[$date]['latest_log_time'])) {
                        $daily[$date]['latest_log_time'] = $logLatest?->created_at;
                    }
                }
            }
        }
    
        // Now calculate logs only once per link-date
        foreach ($logCache as $date => $links) {
            foreach ($links as $linkId => $ranges) {
                $link = Link::find($linkId);
                $baseColumn = $link->base_logs_type ?? 'log';
    
                // Merge ranges
                usort($ranges, fn($a, $b) => $a[0]->timestamp <=> $b[0]->timestamp);
                $merged = [];
                foreach ($ranges as [$start, $end]) {
                    if (empty($merged) || $start > $merged[count($merged) - 1][1]) {
                        $merged[] = [$start, $end];
                    } else {
                        $merged[count($merged) - 1][1] = max($merged[count($merged) - 1][1], $end);
                    }
                }
    
                // Fetch first and last logs
                $firstLog = null;
                $lastLog = null;
    
                foreach ($merged as [$start, $end]) {
                    $first = \DB::table('link_stats')
                        ->where('link_id', $linkId)
                        ->whereBetween('created_at', [$start, $end])
                        ->orderBy('created_at')
                        ->value($baseColumn);
    
                    $last = \DB::table('link_stats')
                        ->where('link_id', $linkId)
                        ->whereBetween('created_at', [$start, $end])
                        ->orderByDesc('created_at')
                        ->value($baseColumn);
    
                    if (!is_null($first) && is_null($firstLog)) $firstLog = $first;
                    if (!is_null($last)) $lastLog = $last;
                }
    
                $logs = (!is_null($firstLog) && !is_null($lastLog)) ? ($lastLog - $firstLog) : 0;
                $daily[$date]['links'][$linkId]['logs'] = $logs;

                $assignedUserIds = \DB::table('user_link_assignments')
                    ->where('link_id', $linkId)
                    ->whereDate('assigned_at', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('unassigned_at')
                        ->orWhereDate('unassigned_at', '>=', $date);
                    })
                    ->pluck('user_id');

                $totalLinkSpending = \DB::table('network_profile_stats as stats')
                    ->join('network_profiles as np', 'np.id', '=', 'stats.profile_id')
                    ->whereIn('np.user_id', $assignedUserIds)
                    ->whereDate('stats.date', $date)
                    ->selectRaw('SUM(stats.opening_balance + stats.topup_today - COALESCE(stats.closing_balance, stats.current_balance)) as total_spending')
                    ->value('total_spending') ?? 0;
    
                $overrideCr = DailyProfitOverride::where('link_id', $linkId)->where('date', $date)->value('override_cr');
                $dynamicCr = $totalLinkSpending > 0 ? round($logs / $totalLinkSpending, 4) : 0;
    
                $daily[$date]['links'][$linkId]['dynamic_cr'] = $dynamicCr;
                $daily[$date]['links'][$linkId]['cr'] = $overrideCr ?? $dynamicCr;
    
                if ($overrideCr) {
                    $daily[$date]['links'][$linkId]['override_cr'] = $overrideCr;
                }
            }
        }

        foreach ($daily as &$row) {
            $linkLogs = array_column($row['links'], 'logs');
            $linkCr = array_column($row['links'], 'cr');
            $sumLogs = array_sum($linkLogs);
            $sumCr = array_sum($linkCr);

            $row['total_logs'] = $sumLogs;
            $row['cr'] = $sumCr;

            if (!empty($row['oldest_snapshot_time'])) {
                $row['oldest_snapshot_time'] = Carbon::parse($row['oldest_snapshot_time'])->diffForHumans();
            }
            if (!empty($row['latest_log_time'])) {
                $row['latest_log_time'] = Carbon::parse($row['latest_log_time'])->diffForHumans();
            }
        }
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
