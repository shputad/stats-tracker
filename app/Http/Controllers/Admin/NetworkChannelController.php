<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NetworkChannel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NetworkChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/NetworkChannels/Index', [
            'channels' => NetworkChannel::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/NetworkChannels/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:network_channels',
            'website' => 'required|url',
            'has_api' => 'required|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        NetworkChannel::create($request->all());

        return redirect()->route('admin.network-channels.index');
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
    public function edit(NetworkChannel $networkChannel)
    {
        return Inertia::render('Admin/NetworkChannels/Edit', [
            'channel' => $networkChannel,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NetworkChannel $networkChannel)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:network_channels,name,' . $networkChannel->id,
            'website' => 'required|url',
            'has_api' => 'required|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $networkChannel->update($request->all());

        return redirect()->route('admin.network-channels.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NetworkChannel $networkChannel)
    {
        $networkChannel->delete();

        return redirect()->route('admin.network-channels.index');
    }
}
