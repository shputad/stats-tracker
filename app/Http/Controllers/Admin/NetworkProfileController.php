<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NetworkProfile;
use App\Models\NetworkChannel;
use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NetworkProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/NetworkProfiles/Index', [
            'profiles' => NetworkProfile::with('networkChannel', 'link', 'user')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/NetworkProfiles/Create', [
            'channels' => NetworkChannel::all(),
            'links' => Link::all(),
            'users' => User::where('id', '!=', Auth()->id())->where('status', 'active')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'channel_id' => 'required|exists:network_channels,id',
            'link_id' => 'required|exists:links,id',
            'account_id' => 'required|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        NetworkProfile::create($request->all());

        return redirect()->route('admin.network-profiles.index');
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
    public function edit(NetworkProfile $networkProfile)
    {
        return Inertia::render('Admin/NetworkProfiles/Edit', [
            'profile' => $networkProfile->load('networkChannel', 'link', 'user'),
            'channels' => NetworkChannel::all(),
            'links' => Link::all(),
            'users' => User::where('id', '!=', Auth()->id())->where('status', 'active')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NetworkProfile $networkProfile)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'channel_id' => 'required|exists:network_channels,id',
            'link_id' => 'required|exists:links,id',
            'account_id' => 'required|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $networkProfile->update($request->all());

        return redirect()->route('admin.network-profiles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NetworkProfile $networkProfile)
    {
        $networkProfile->delete();

        return redirect()->route('admin.network-profiles.index');
    }
}
