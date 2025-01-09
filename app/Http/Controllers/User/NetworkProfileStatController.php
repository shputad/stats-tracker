<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NetworkProfile;
use App\Models\NetworkProfileStat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NetworkProfileStatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(NetworkProfile $profile)
    {
        return Inertia::render('User/NetworkProfiles/Stats/Index', [
            'profile' => $profile,
            'stats' => $profile->stats, // Assuming a `stats` relationship exists
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
}
