<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NetworkProfile;
use App\Models\NetworkProfileStat;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class NetworkProfileStatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(NetworkProfile $networkProfile)
    {
        $updateInterval = Setting::where('key', 'profile_stats_update_interval')->value('value') ?? 10;

        $stats = $networkProfile->stats()
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
                    'id' => $stat->id,
                    'date' => $stat->date,
                    'opening_balance' => $opening,
                    'topup_today' => $topup,
                    'closing_balance' => $closing
                ];
            });

        return Inertia::render('User/NetworkProfiles/Stats/Index', [
            'profile' => $networkProfile->load('networkChannel'),
            'stats' => $stats,
            'settings' => ['profile_stats_update_interval' => $updateInterval],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(NetworkProfile $profile)
    {
        return Inertia::render('User/NetworkProfiles/Stats/Create', [
            'profile' => $profile,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, NetworkProfile $profile)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $profile->stats()->create($request->all());

        return redirect()->route('user.network-profiles.stats.index', $profile);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NetworkProfile $profile, NetworkProfileStat $stat)
    {
        return Inertia::render('User/NetworkProfiles/Stats/Edit', [
            'profile' => $profile,
            'stat' => $stat,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NetworkProfile $profile, NetworkProfileStat $stat)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $stat->update($request->all());

        return redirect()->route('user.network-profiles.stats.index', $profile);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NetworkProfile $profile, NetworkProfileStat $stat)
    {
        $stat->delete();

        return redirect()->route('user.network-profiles.stats.index', $profile);
    }

    public function updateTopup(Request $request, NetworkProfile $profile, $date)
    {
        $validated = $request->validate([
            'topup_today' => 'required|numeric|min:0',
        ]);

        $stat = $profile->stats()->firstOrCreate(
            ['date' => $date],
            [
                'opening_balance' => 0,
                'closing_balance' => 0,
                'current_balance' => 0,
                'topup_today' => 0,
            ]
        );

        $stat->topup_today = $validated['topup_today'];
        $stat->save();

        return redirect()->back()->with('success', 'Top-up updated successfully.');
    }
}
