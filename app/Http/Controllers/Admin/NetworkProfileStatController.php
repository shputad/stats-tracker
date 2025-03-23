<?php

namespace App\Http\Controllers\Admin;

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

        return Inertia::render('Admin/NetworkProfiles/Stats/Index', [
            'profile' => $networkProfile->load('networkChannel'),
            'stats' => $stats,
            'settings' => ['profile_stats_update_interval' => $updateInterval],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(NetworkProfile $networkProfile)
    {
        return Inertia::render('Admin/NetworkProfiles/Stats/Create', [
            'profile' => $networkProfile,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, NetworkProfile $networkProfile)
    {
        $request->validate([
            'type' => 'required|string|max:255', // Example field
            'amount' => 'required|numeric',
        ]);

        $networkProfile->stats()->create($request->all());

        return redirect()->route('admin.network-profiles.stats.index', $networkProfile);
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
    public function edit(NetworkProfile $networkProfile, NetworkProfileStat $stat)
    {
        return Inertia::render('Admin/NetworkProfiles/Stats/Edit', [
            'profile' => $networkProfile,
            'stat' => $stat,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NetworkProfile $networkProfile, NetworkProfileStat $stat)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $stat->update($request->all());

        return redirect()->route('admin.network-profiles.stats.index', $networkProfile);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NetworkProfile $networkProfile, NetworkProfileStat $stat)
    {
        $stat->delete();

        return redirect()->route('admin.network-profiles.stats.index', $networkProfile);
    }

    public function updateTopup(Request $request, NetworkProfile $networkProfile, $date)
    {
        $validated = $request->validate([
            'topup_today' => 'required|numeric|min:0',
        ]);

        $stat = $networkProfile->stats()->firstOrCreate(
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
