<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NetworkProfileStat;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Fetch network profile stats for the authenticated user.
     */
    public function getNetworkProfileStats(Request $request)
    {
        $user = $request->user();

        // Fetch stats for the user's profiles
        $stats = NetworkProfileStat::with('profile.networkChannel', 'profile.link')
            ->whereHas('profile', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return response()->json($stats);
    }

    /**
     * Add a top-up record for a network profile.
     */
    public function addTopup(Request $request)
    {
        $validated = $request->validate([
            'profile_id' => 'required|exists:network_profiles,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Add the top-up to the profile's stats
        $stat = NetworkProfileStat::where('profile_id', $validated['profile_id'])->first();

        if (!$stat) {
            return response()->json(['message' => 'Profile stats not found'], 404);
        }

        $stat->increment('topups', $validated['amount']);

        return response()->json(['message' => 'Top-up added successfully', 'stat' => $stat]);
    }

    /**
     * (Optional) Fetch overall stats for admin or user-specific stats.
     */
    public function getOverallStats(Request $request)
    {
        $user = $request->user();

        // Check role and return relevant stats
        if ($user->hasRole('admin')) {
            // Fetch stats for all users (admin view)
            $stats = NetworkProfileStat::with('profile.networkChannel', 'profile.link')->get();
        } else {
            // Fetch stats for the current user only
            $stats = NetworkProfileStat::with('profile.networkChannel', 'profile.link')
                ->whereHas('profile', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
        }

        return response()->json($stats);
    }
}
