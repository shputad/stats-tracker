<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\NetworkChannelController as AdminNetworkChannelController;
use App\Http\Controllers\Admin\LinkController as AdminLinkController;
use App\Http\Controllers\Admin\LinkStatController as AdminLinkStatController;
use App\Http\Controllers\Admin\NetworkProfileController as AdminNetworkProfileController;
use App\Http\Controllers\Admin\NetworkProfileStatController as AdminNetworkProfileStatController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\User\LinkStatController as UserLinkStatController;
use App\Http\Controllers\User\NetworkProfileController as UserNetworkProfileController;
use App\Http\Controllers\User\NetworkProfileStatController as UserNetworkProfileStatController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return app(AdminController::class)->index();
    }

    if ($user->hasRole('user')) {
        return app(UserController::class)->index();
    }

    abort(403, 'Unauthorized');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // CRUD for Network Channels
    Route::resource('network-channels', AdminNetworkChannelController::class);

    // CRUD for Links
    Route::resource('links', AdminLinkController::class);

    // CRUD for Network Profiles
    Route::resource('network-profiles', AdminNetworkProfileController::class);

    // CRUD for Users
    Route::resource('users', AdminUserController::class);

    // Settings
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Sub-Routes
    Route::resource('links.stats', AdminLinkStatController::class)->only(['index']);
    Route::resource('network-profiles/{profile}/stats', AdminNetworkProfileStatController::class);
});

Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    // CRUD for Network Profiles
    Route::resource('network-profiles', UserNetworkProfileController::class)->except(['show']);

    // Sub-Routes
    Route::resource('links/{link}/stats', UserLinkStatController::class)->only(['index', 'show']);
    Route::resource('network-profiles/{profile}/stats', UserNetworkProfileStatController::class);
});


require __DIR__.'/auth.php';
