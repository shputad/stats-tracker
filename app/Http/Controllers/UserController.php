<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\LinkStat;
use App\Models\NetworkProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $profileIds = $user->networkProfiles()->pluck('id');
        $linkIds = $user->networkProfiles()->pluck('link_id')->filter()->unique();

        $links = Link::whereIn('id', $linkIds)->get();
        $profiles = $user->networkProfiles()->with('link')->get();

        $recentStats = LinkStat::whereIn('link_id', $linkIds)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($stat) {
                return [
                    'id' => $stat->id,
                    'log' => $stat->log,
                    'created_at' => $stat->created_at,
                    'link_name' => optional($stat->link)->name ?? 'N/A',
                ];
            });

        return Inertia::render('User/Dashboard', [
            'links' => $links,
            'profiles' => $profiles,
            'recentStats' => $recentStats,
        ]);
    }

    public function leaveImpersonation()
    {
        if (session()->has('impersonate')) {
            $adminId = session()->pull('impersonate');
            Auth::loginUsingId($adminId);
            session()->forget('impersonate');

            return redirect()->route('admin.users.index')->with('success', 'Impersonation ended.');
        }

        return redirect()->route('admin.users.index');
    }
}
