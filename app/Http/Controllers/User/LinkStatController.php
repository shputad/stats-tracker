<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LinkStatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Link $link)
    {
        return Inertia::render('User/Links/Stats/Index', [
            'link' => $link,
            'stats' => $link->stats, // Assuming a `stats` relationship exists
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Link $link, $statId)
    {
        $stat = $link->stats()->findOrFail($statId); // Adjust as per your stat model

        return Inertia::render('User/Links/Stats/Show', [
            'link' => $link,
            'stat' => $stat,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
