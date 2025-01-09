<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NetworkChannel;
use App\Models\NetworkProfile;
use App\Models\Link;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NetworkProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('User/NetworkProfiles/Index', [
            'profiles' => NetworkProfile::where('user_id', auth()->id())->with('networkChannel', 'link')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('User/NetworkProfiles/Create', [
            'channels' => NetworkChannel::all(),
            'links' => Link::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'channel_id' => 'required|exists:network_channels,id',
            'link_id' => 'required|exists:links,id',
            'account_id' => 'required|string|max:255',
        ]);

        NetworkProfile::create([
            'user_id' => auth()->id(),
            'channel_id' => $request->channel_id,
            'link_id' => $request->link_id,
            'account_id' => $request->account_id,
        ]);

        return redirect()->route('user.network-profiles.index');
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
    public function edit(NetworkProfile $profile)
    {
        $this->authorize('update', $profile);

        return Inertia::render('User/NetworkProfiles/Edit', [
            'profile' => $profile,
            'channels' => NetworkChannel::all(),
            'links' => Link::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NetworkProfile $profile)
    {
        $this->authorize('update', $profile);

        $request->validate([
            'channel_id' => 'required|exists:network_channels,id',
            'link_id' => 'required|exists:links,id',
            'account_id' => 'required|string|max:255',
        ]);

        $profile->update($request->only('channel_id', 'link_id', 'account_id'));

        return redirect()->route('user.network-profiles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NetworkProfile $profile)
    {
        $this->authorize('delete', $profile);

        $profile->delete();

        return redirect()->route('user.network-profiles.index');
    }
}
