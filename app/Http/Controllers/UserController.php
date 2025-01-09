<?php

namespace App\Http\Controllers;

use App\Models\NetworkProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Dashboard', [
            'profiles' => NetworkProfile::where('user_id', auth()->id())->with('networkChannel', 'link')->get(),
        ]);
    }
}
