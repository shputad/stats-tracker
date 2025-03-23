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
            
                if ($diffMinutes > 10) {
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
        $dateKey = $takenAt->toDateString();

        // Create snapshot
        $snapshot = NetworkProfileSnapshot::create([
            'profile_id' => $networkProfile->id,
            'balance' => $balance,
            'taken_at' => $takenAt,
        ]);

        // Update stats table
        $stat = $networkProfile->stats()->firstOrCreate(
            ['date' => $dateKey],
            [
                'opening_balance' => $balance, // If first snapshot of day, this will be overridden
                'closing_balance' => $balance,
                'current_balance' => $balance,
                'topup_today' => 0,
            ]
        );

        // Opening balance: use earliest snapshot of the day
        $opening = $networkProfile->snapshots()
            ->whereDate('taken_at', $dateKey)
            ->orderBy('taken_at')
            ->value('balance') ?? $balance;

        $stat->update([
            'opening_balance' => $opening,
            'closing_balance' => $balance,
            'current_balance' => $balance,
        ]);

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
        $dateKey = $takenAt->toDateString();

        // Update stats after editing
        $latestBalance = $networkProfile->snapshots()
            ->whereDate('taken_at', $dateKey)
            ->latest('taken_at')
            ->value('balance') ?? $request->balance;

        $opening = $networkProfile->snapshots()
            ->whereDate('taken_at', $dateKey)
            ->orderBy('taken_at')
            ->value('balance') ?? $request->balance;

        $stat = $networkProfile->stats()->firstOrCreate(
            ['date' => $dateKey],
            [
                'opening_balance' => $opening,
                'closing_balance' => $latestBalance,
                'current_balance' => $latestBalance,
                'topup_today' => 0,
            ]
        );

        $stat->update([
            'opening_balance' => $opening,
            'closing_balance' => $latestBalance,
            'current_balance' => $latestBalance,
        ]);

        return redirect()->route('user.network-profiles.snapshots.index', $networkProfile->id)
            ->with('success', 'Snapshot updated successfully and stats adjusted.');
    }

    /**
     * Delete a snapshot.
     */
    public function destroy(NetworkProfile $networkProfile, NetworkProfileSnapshot $snapshot)
    {
        $dateKey = Carbon::parse($snapshot->taken_at)->toDateString();
        $snapshot->delete();

        // Recalculate stats for the day
        $snapshotsOfDay = $networkProfile->snapshots()
            ->whereDate('taken_at', $dateKey)
            ->orderBy('taken_at')
            ->get();

        if ($snapshotsOfDay->isEmpty()) {
            // If no more snapshots exist for the day, optionally delete the stat
            $networkProfile->stats()->where('date', $dateKey)->delete();
        } else {
            $opening = $snapshotsOfDay->first()->balance;
            $closing = $snapshotsOfDay->last()->balance;

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
        }

        return redirect()->route('user.network-profiles.snapshots.index', $networkProfile->id)
            ->with('success', 'Snapshot deleted and stats updated successfully.');
    }
}
