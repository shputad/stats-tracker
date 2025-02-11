<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Settings/Edit', [
            'settings' => Setting::all()->pluck('value', 'key'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'link_stats_update_interval' => 'required|integer|min:1',
            'profile_stats_update_interval' => 'required|integer|min:1',
            'stats_per_page' => 'required|integer|min:25',
            'capmonster_api_key' => 'nullable|string|max:255',
            'twocaptcha_api_key' => 'nullable|string|max:255',
            'google_cloud_run_url' => 'nullable|string|max:255',
            'proxy_type' => 'nullable|string|max:255',
            'proxy_address' => 'nullable|string|max:255',
            'proxy_port' => 'nullable|string|max:255',
            'proxy_login' => 'nullable|string|max:255',
            'proxy_password' => 'nullable|string|max:255',
        ]);

        foreach ($request->all() as $key => $value) {
            if (in_array(
                $key,
                [
                    'link_stats_update_interval',
                    'profile_stats_update_interval',
                    'stats_per_page',
                    'capmonster_api_key',
                    'twocaptcha_api_key',
                    'google_cloud_run_url',
                    'proxy_type',
                    'proxy_address',
                    'proxy_port',
                    'proxy_login',
                    'proxy_password'
                ]
            )) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }
}
