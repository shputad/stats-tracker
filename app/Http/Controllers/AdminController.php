<?php

namespace App\Http\Controllers;

use App\Models\NetworkChannel;
use App\Models\Link;
use App\Models\NetworkProfile;
use App\Models\User;
use App\Models\DailyProfitOverride;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

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
        $userId = $request->input('user_id', $request->user()->id);

        $user = User::findOrFail($userId);

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
            'selectedUserId' => $userId,
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function dailyProfit(Request $request)
    {
        $userId = $request->input('user_id', $request->user()->id);
        $user = User::findOrFail($userId);
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

        return Inertia::render('Admin/DailyProfit', [
            'summary' => collect($daily)->sortByDesc('date')->values()->all(),
            'users' => User::select('id', 'name')->get(),
            'selectedUserId' => $userId,
            'profitPercentage' => $profitPercentage,
        ]);
    }

    public function updateDailyProfitOverride(Request $request)
    {
        $validated = $request->validate([
            'link_id' => 'required|exists:links,id',
            'date' => 'required|date',
            'override_cr' => 'required|numeric|min:0|max:100',
        ]);

        DailyProfitOverride::updateOrCreate(
            [
                'link_id' => $validated['link_id'],
                'date' => $validated['date'],
            ],
            ['override_cr' => $validated['override_cr']]
        );

        return redirect()->back()->with('success', 'CR override saved.');
    }

    public function deleteDailyProfitOverride(Request $request)
    {
        $validated = $request->validate([
            'link_id' => 'required|exists:links,id',
            'date' => 'required|date',
        ]);

        DailyProfitOverride::where('link_id', $validated['link_id'])
            ->where('date', $validated['date'])
            ->delete();

        return redirect()->back()->with('success', 'Ok.');
    }
}
