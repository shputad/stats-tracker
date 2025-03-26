<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\NetworkChannelController as AdminNetworkChannelController;
use App\Http\Controllers\Admin\LinkController as AdminLinkController;
use App\Http\Controllers\Admin\LinkStatController as AdminLinkStatController;
use App\Http\Controllers\Admin\NetworkProfileController as AdminNetworkProfileController;
use App\Http\Controllers\Admin\NetworkProfileStatController as AdminNetworkProfileStatController;
use App\Http\Controllers\Admin\NetworkProfileSnapshotController as AdminNetworkProfileSnapshotController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ToolsController as AdminToolsController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\User\LinkController as UserLinkController;
use App\Http\Controllers\User\LinkStatController as UserLinkStatController;
use App\Http\Controllers\User\NetworkProfileController as UserNetworkProfileController;
use App\Http\Controllers\User\NetworkProfileStatController as UserNetworkProfileStatController;
use App\Http\Controllers\User\NetworkProfileSnapshotController as UserNetworkProfileSnapshotController;
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

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // CRUD for Network Channels
    Route::resource('network-channels', AdminNetworkChannelController::class);

    // CRUD for Links
    Route::resource('links', AdminLinkController::class);

    // CRUD for Network Profiles
    Route::resource('network-profiles', AdminNetworkProfileController::class);

    // CRUD for Users
    Route::resource('users', AdminUserController::class);

    Route::post('/impersonate/{user}', [AdminUserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('/impersonate-leave', [AdminUserController::class, 'leaveImpersonation'])->name('users.leave-impersonation');

    // Settings
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Tools
    Route::get('tools', [AdminToolsController::class, 'index'])->name('tools.index');
    Route::get('tools/command-builder', [AdminToolsController::class, 'commandBuilder'])->name('tools.commandbuilder.index');
    Route::get('tools/lander-templates', [AdminToolsController::class, 'landerTemplates'])->name('tools.landertemplates.index');
    Route::get('tools/lander-builder', [AdminToolsController::class, 'landerBuilder'])->name('tools.landerbuilder.index');

    // Command Builder API
    Route::post('tools/command-builder/generate', [AdminToolsController::class, 'generateCommand'])->name('tools.commandbuilder.generate');
    Route::post('tools/command-builder/export', [AdminToolsController::class, 'exportCommand'])->name('tools.commandbuilder.export');
    Route::post('tools/command-builder/test', [AdminToolsController::class, 'testCommand'])->name('tools.commandbuilder.test');

    // Lander Templates
    Route::get('tools/lander-templates-json', [AdminToolsController::class, 'landerTemplatesJson'])->name('tools.landertemplates.json');
    Route::post('tools/lander-templates', [AdminToolsController::class, 'storeLanderTemplate'])->name('tools.landertemplates.store');
    Route::get('tools/lander-templates/preview', [AdminToolsController::class, 'previewLanderTemplate'])->name('tools.landertemplates.preview');
    Route::delete('tools/lander-templates/{filename}', [AdminToolsController::class, 'deleteLanderTemplate'])->name('tools.landertemplates.destroy');

    // Sub-Routes
    Route::resource('links.stats', AdminLinkStatController::class);
    Route::resource('network-profiles.snapshots', AdminNetworkProfileSnapshotController::class);
    Route::resource('network-profiles.stats', AdminNetworkProfileStatController::class);
    Route::put('network-profiles/{profile}/stats/{date}/topup', [AdminNetworkProfileStatController::class, 'updateTopup'])->name('network-profiles.stats.updateTopup');

    Route::get('/daily-summary', [AdminController::class, 'dailySummary'])->name('daily-summary');
    Route::get('/daily-profit', [AdminController::class, 'dailyProfit'])->name('daily-profit');
    Route::post('/daily-profit-override', [AdminController::class, 'updatedailyProfitOverride'])->name('daily-profit.override');

    // Profile
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:user'])->name('user.')->group(function () {
    // CRUD for Network Profiles
    Route::resource('network-profiles', UserNetworkProfileController::class)->except(['show']);

    // Links
    Route::resource('links', UserLinkController::class)->only(['index', 'show']);

    // Sub-Routes
    Route::resource('links.stats', UserLinkStatController::class)->only(['index', 'show']);
    Route::resource('network-profiles.snapshots', UserNetworkProfileSnapshotController::class);
    Route::resource('network-profiles.stats', UserNetworkProfileStatController::class);
    Route::put('network-profiles/{profile}/stats/{date}/topup', [UserNetworkProfileStatController::class, 'updateTopup'])->name('network-profiles.stats.updateTopup');


    Route::get('/daily-summary', [UserController::class, 'dailySummary'])->name('daily-summary');
    Route::get('/daily-profit', [UserController::class, 'dailyProfit'])->name('daily-profit');
    Route::post('/impersonate-leave', [UserController::class, 'leaveImpersonation'])->name('leave-impersonation');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
