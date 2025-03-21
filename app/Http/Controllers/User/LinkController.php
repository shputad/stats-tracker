<?php

namespace App\Http\Controllers\User;

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
        $user = auth()->user();

        // Get all link IDs attached to the user's network profiles
        $linkIds = $user->networkProfiles()->pluck('link_id')->filter()->unique();

        // Fetch only those links
        $links = Link::whereIn('id', $linkIds)->orderBy('created_at', 'desc')->get();

        return Inertia::render('User/Links/Index', [
            'links' => $links,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
}
