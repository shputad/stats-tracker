<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Links/Index', [
            'links' => Link::orderBy('created_at', 'desc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Links/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'build_tag' => 'required|string|max:255',
            'url' => 'required|url',
            'type' => 'required|in:a,b,c,d',
            'status' => 'required|in:active,inactive',
            'api_url' => 'nullable|url',
            'base_logs_type' => 'nullable|in:log,detailed_log',
        ]);

        Link::create($request->only(['name', 'build_tag', 'url', 'type', 'status', 'api_url', 'base_logs_type']));

        return redirect()->route('admin.links.index');
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
    public function edit(Link $link)
    {
        return Inertia::render('Admin/Links/Edit', [
            'link' => $link
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Link $link)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'build_tag' => 'required|string|max:255',
            'url' => 'required|url',
            'type' => 'required|in:a,b,c,d',
            'status' => 'required|in:active,inactive',
            'api_url' => 'nullable|url',
            'base_logs_type' => 'nullable|in:log,detailed_log',
        ]);

        $link->update($request->only(['name', 'build_tag', 'url', 'type', 'status', 'api_url', 'base_logs_type']));

        return redirect()->route('admin.links.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Link $link)
    {
        $link->delete();

        return redirect()->route('admin.links.index');
    }
}
