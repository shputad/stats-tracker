<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::with('roles')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,inactive',
            'profit_percentage' => 'required|integer|between:0,100',
            'min_daily_profit_cap' => 'nullable|integer',
            'special_profit_percentage' => 'nullable|integer|between:0,100'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'status' => $request->status,
            'profit_percentage' => $request->profit_percentage,
            'min_daily_profit_cap' => $request->min_daily_profit_cap,
            'special_profit_percentage' => $request->special_profit_percentage
        ]);

        $user->assignRole($request->input('role', 'user'));

        return redirect()->route('admin.users.index');
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
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'status' => 'required|in:active,inactive',
            'profit_percentage' => 'required|integer|between:0,100',
            'min_daily_profit_cap' => 'nullable|integer',
            'special_profit_percentage' => 'nullable|integer|between:0,100',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
            'profit_percentage' => $request->profit_percentage,
            'min_daily_profit_cap' => $request->min_daily_profit_cap,
            'special_profit_percentage' => $request->special_profit_percentage
        ]);

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('admin.users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function impersonate(User $user)
    {
        if (Auth::user()->hasRole('admin')) {
            session(['impersonate' => Auth::id()]);
            Auth::login($user);
        }

        return redirect()->route('dashboard');
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
