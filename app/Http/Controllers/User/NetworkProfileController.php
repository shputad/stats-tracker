<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NetworkChannel;
use App\Models\NetworkProfile;
use App\Models\Link;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NetworkProfileController extends Controller
{
    use AuthorizesRequests;

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
            'account_id' => 'required|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        NetworkProfile::create([
            'user_id' => auth()->id(),
            'channel_id' => $request->channel_id,
            'link_id' => null,
            'account_id' => $request->account_id,
            'api_key' => $request->api_key,
            'status' => $request->status,
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
    public function edit(NetworkProfile $networkProfile)
    {
        $this->authorize('update', $networkProfile);

        return Inertia::render('User/NetworkProfiles/Edit', [
            'profile' => $networkProfile,
            'channels' => NetworkChannel::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NetworkProfile $networkProfile)
    {
        $this->authorize('update', $networkProfile);

        $request->validate([
            'channel_id' => 'required|exists:network_channels,id',
            'account_id' => 'required|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $networkProfile->update($request->only('channel_id', 'account_id', 'api_key', 'status'));

        return redirect()->route('user.network-profiles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NetworkProfile $networkProfile)
    {
        $this->authorize('delete', $networkProfile);

        $networkProfile->delete();

        return redirect()->route('user.network-profiles.index');
    }
}
