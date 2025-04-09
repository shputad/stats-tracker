<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => Auth::user() ? array_merge(
                    $request->user()->toArray(),
                    [
                        'isImpersonating' => session()->has('impersonate'),
                    ]
                ) : $request->user(),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'command' => session('command'),
                'test_output' => session('test_output'),
            ],
            'unlinkedUsersCount' => function () use ($request) {
                $user = $request->user();
                return $user && $user->hasRole('admin')
                    ? User::whereNull('link_id')->count()
                    : null;
            },
        ];
    }
}
