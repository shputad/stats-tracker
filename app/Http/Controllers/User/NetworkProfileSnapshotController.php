<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NetworkProfile;
use App\Models\NetworkProfileSnapshot;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class NetworkProfileSnapshotController extends Controller
{
    /**
     * Display a listing of the snapshots.
     */
    public function index(NetworkProfile $networkProfile, Request $request)
    {
        $statsPerPage = Setting::where('key', 'stats_per_page')->value('value') ?? 25;
        $snapshotUpdateInterval = Setting::where('key', 'profile_stats_update_interval')->value('value') ?? 10;
        $perPage = $request->get('per_page', $statsPerPage);

        $snapshotsPaginator = $networkProfile->snapshots()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Compute balance changes over different time intervals
        $snapshotsWithDifferences = $snapshotsPaginator->getCollection()->map(function ($snapshot, $index) use ($networkProfile, $snapshotsPaginator) {
            $takenAt = Carbon::parse($snapshot->taken_at); // Ensure it's a Carbon instance

            // Balance 1 hour ago
            $balanceBeforeHour = $networkProfile->snapshots()
                ->whereBetween('taken_at', [$takenAt->copy()->subMinutes(60)->startOfMinute(), $takenAt->copy()->startOfMinute()])
                ->orderBy('taken_at', 'asc')
                ->value('balance') ?? 0;

            // Balance at the start of today
            $balanceBeforeToday = $networkProfile->snapshots()
                ->whereDate('taken_at', $takenAt->toDateString())
                ->orderBy('taken_at', 'asc')
                ->value('balance') ?? 0;

            // Determine if this is the first snapshot in the hour
            $isFirstSnapshotOfHour = !$networkProfile->snapshots()
                ->where('taken_at', '<', $takenAt)
                ->whereBetween('taken_at', [$takenAt->copy()->startOfHour(), $takenAt->copy()->endOfHour()])
                ->exists();

            $previousSnapshot = $networkProfile->snapshots()
                ->where('taken_at', '<', $takenAt)
                ->orderBy('taken_at', 'desc')
                ->first();
            
            $diffMinutes = null;
            $diffAmount = null;
            $timeDiffTooltip = null;
            
            if ($previousSnapshot) {
                $previousTakenAt = Carbon::parse($previousSnapshot->taken_at);
                $diffMinutes = $previousTakenAt->diffInMinutes($takenAt);
                $diffAmount = $snapshot->balance - $previousSnapshot->balance;
            
                if ($diffMinutes >= 11) {
                    $timeDiffTooltip = "Last snapshot was " . $previousTakenAt->diffForHumans($takenAt);
                }
            }

            return [
                'id' => $snapshot->id,
                'balance' => $snapshot->balance,
                'taken_at' => $snapshot->taken_at,
                'last_10_minutes_diff' => $diffAmount,
                'last_10_minutes_tooltip' => $timeDiffTooltip,
                'last_hour_diff' => $isFirstSnapshotOfHour ? ($snapshot->balance - $balanceBeforeHour) : null,
                'today_diff' => $snapshot->balance - $balanceBeforeToday
            ];
        });

        $snapshotsPaginator->setCollection($snapshotsWithDifferences);

        $dailyStats = $networkProfile->stats()
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(function ($stat) {
                return Carbon::parse($stat->date)->toDateString();
            })
            ->map(function ($group) {
                $stat = $group->first();
                $opening = (float) $stat->opening_balance;
                $topup = (float) $stat->topup_today;
                $closing = (float) ($stat->closing_balance ?? $stat->current_balance ?? 0);

                return [
                    'opening_balance' => $opening,
                    'topup_today' => $topup,
                    'closing_balance' => $closing,
                ];
            });

        return Inertia::render('User/NetworkProfiles/Snapshots/Index', [
            'profile' => $networkProfile->load('networkChannel'),
            'snapshots' => $snapshotsPaginator,
            'settings' => ['profile_stats_update_interval' => $snapshotUpdateInterval],
            'stats' => $dailyStats,
        ]);
    }

    /**
     * Show the form for creating a new snapshot.
     */
    public function create(NetworkProfile $networkProfile)
    {
        return Inertia::render('User/NetworkProfiles/Snapshots/Create', [
            'profile' => $networkProfile->load('networkChannel')
        ]);
    }

    /**
     * Store a newly created snapshot manually.
     */
    public function store(Request $request, NetworkProfile $networkProfile)
    {
        $request->validate([
            'balance' => 'required|numeric|min:0',
        ]);

        $balance = $request->balance;
        $takenAt = now();
        $today = $takenAt->toDateString();

        NetworkProfileSnapshot::create([
            'profile_id' => $networkProfile->id,
            'balance' => $balance,
            'taken_at' => $takenAt,
        ]);

        $stat = $networkProfile->stats()->firstOrNew(['date' => $today]);
        $isFirst = is_null($stat->opening_balance);

        if ($isFirst) {
            $stat->opening_balance = $balance;
        }

        $stat->closing_balance = $balance;
        $stat->current_balance = $balance;
        $stat->save();

        // Sync last existing stat if this is first of the day
        if ($isFirst) {
            $lastStat = $networkProfile->stats()
                ->where('date', '<', $today)
                ->orderByDesc('date')
                ->first();

            if ($lastStat && $lastStat->closing_balance != $stat->opening_balance) {
                $lastStat->closing_balance = $stat->opening_balance;
                $lastStat->save();
            }
        }

        return redirect()->route('user.network-profiles.snapshots.index', $networkProfile->id)
            ->with('success', 'Snapshot added successfully and stats updated.');
    }

    /**
     * Display a specific snapshot.
     */
    public function show(NetworkProfile $networkProfile, NetworkProfileSnapshot $snapshot)
    {
        return Inertia::render('User/NetworkProfiles/Snapshots/Show', [
            'profile' => $networkProfile,
            'snapshot' => $snapshot
        ]);
    }

    /**
     * Show the form for editing a snapshot.
     */
    public function edit(NetworkProfile $networkProfile, NetworkProfileSnapshot $snapshot)
    {
        return Inertia::render('User/NetworkProfiles/Snapshots/Edit', [
            'profile' => $networkProfile->load('networkChannel'),
            'snapshot' => $snapshot
        ]);
    }

    /**
     * Update an existing snapshot.
     */
    public function update(Request $request, NetworkProfile $networkProfile, NetworkProfileSnapshot $snapshot)
    {
        $request->validate([
            'balance' => 'required|numeric|min:0',
        ]);

        $snapshot->update([
            'balance' => $request->balance,
        ]);

        $takenAt = Carbon::parse($snapshot->taken_at);
        $today = $takenAt->toDateString();

        $opening = $networkProfile->snapshots()
            ->whereDate('taken_at', $today)
            ->orderBy('taken_at')
            ->value('balance') ?? $request->balance;

        $latestBalance = $networkProfile->snapshots()
            ->whereDate('taken_at', $today)
            ->latest('taken_at')
            ->value('balance') ?? $request->balance;

        $stat = $networkProfile->stats()->firstOrNew(['date' => $today]);
        $isFirstSnapshotOfDay = !$networkProfile->snapshots()
            ->whereDate('taken_at', $takenAt->toDateString())
            ->where('taken_at', '<', $takenAt)
            ->exists();

        if ($isFirstSnapshotOfDay) {
            $stat->opening_balance = $opening;
        }

        $stat->closing_balance = $latestBalance;
        $stat->current_balance = $latestBalance;
        $stat->save();

        if ($isFirstSnapshotOfDay) {
            $lastStat = $networkProfile->stats()
                ->where('date', '<', $today)
                ->orderByDesc('date')
                ->first();

            if ($lastStat && $lastStat->closing_balance != $stat->opening_balance) {
                $lastStat->closing_balance = $stat->opening_balance;
                $lastStat->save();
            }
        }

        return redirect()->route('user.network-profiles.snapshots.index', $networkProfile->id)
            ->with('success', 'Snapshot updated successfully and stats adjusted.');
    }

    /**
     * Delete a snapshot.
     */
    public function destroy(NetworkProfile $networkProfile, NetworkProfileSnapshot $snapshot)
    {
        $takenAt = Carbon::parse($snapshot->taken_at);
        $dateKey = $takenAt->toDateString();
        $snapshot->delete();

        $snapshotsToday = $networkProfile->snapshots()
            ->whereDate('taken_at', $dateKey)
            ->orderBy('taken_at')
            ->get();

        if ($snapshotsToday->isEmpty()) {
            // Delete the stat for that day
            $networkProfile->stats()->where('date', $dateKey)->delete();

            // ✅ Find the last date BEFORE this snapshot
            $lastSnapshotBefore = $networkProfile->snapshots()
                ->where('taken_at', '<', $takenAt)
                ->orderByDesc('taken_at')
                ->first();

            // ✅ Find the first snapshot AFTER the deleted one (future day)
            $firstSnapshotAfter = $networkProfile->snapshots()
                ->where('taken_at', '>', $takenAt)
                ->orderBy('taken_at')
                ->first();

            if ($lastSnapshotBefore) {
                $lastDate = Carbon::parse($lastSnapshotBefore->taken_at)->toDateString();
                $stat = $networkProfile->stats()->where('date', $lastDate)->first();

                if ($stat) {
                    $newClosing = $firstSnapshotAfter
                        ? $firstSnapshotAfter->balance
                        : $lastSnapshotBefore->balance;

                    if ($stat->closing_balance != $newClosing) {
                        $stat->update(['closing_balance' => $newClosing]);
                    }
                }
            }
        } else {
            // Recalculate for current date (after deletion)
            $opening = $snapshotsToday->first()->balance;
            $closing = $snapshotsToday->last()->balance;

            $stat = $networkProfile->stats()->firstOrCreate(
                ['date' => $dateKey],
                [
                    'opening_balance' => $opening,
                    'closing_balance' => $closing,
                    'current_balance' => $closing,
                    'topup_today' => 0,
                ]
            );

            $stat->update([
                'opening_balance' => $opening,
                'closing_balance' => $closing,
                'current_balance' => $closing,
            ]);

            // ✅ Also update previous day's closing (to match new opening)
            $lastSnapshotBefore = $networkProfile->snapshots()
                ->where('taken_at', '<', $snapshotsToday->first()->taken_at)
                ->orderByDesc('taken_at')
                ->first();

            if ($lastSnapshotBefore) {
                $lastDate = Carbon::parse($lastSnapshotBefore->taken_at)->toDateString();
                $prevStat = $networkProfile->stats()->where('date', $lastDate)->first();

                if ($prevStat && $prevStat->closing_balance != $opening) {
                    $prevStat->update(['closing_balance' => $opening]);
                }
            }
        }

        return redirect()->route('user.network-profiles.snapshots.index', $networkProfile->id)
            ->with('success', 'Snapshot deleted and stats updated successfully.');
    }
}
