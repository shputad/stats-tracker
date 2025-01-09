<?php

namespace App\Http\Controllers;

use App\Models\NetworkChannel;
use App\Models\Link;
use App\Models\NetworkProfile;
use App\Models\User;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'channelsCount' => NetworkChannel::count(),
            'linksCount' => Link::count(),
            'profilesCount' => NetworkProfile::count(),
            'usersCount' => User::count(),
            'recentLinks' => Link::latest()->take(5)->get(),
            'recentProfiles' => NetworkProfile::with('networkChannel', 'link')->latest()->take(5)->get(),
        ]);
    }
}
