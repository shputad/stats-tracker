<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireToolsPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply this check to /admin/tools and its subroutes
        if ($request->is('admin/tools*')) {
            // If already authenticated, continue
            if ($request->session()->get('tools_authenticated')) {
                return $next($request);
            }

            // Allow access to password form and password submission route
            if (
                $request->is('admin/tools/password') ||
                ($request->is('admin/tools/password') && $request->isMethod('post'))
            ) {
                return $next($request);
            }

            // Otherwise, redirect to password entry page
            return redirect()->route('admin.tools.password');
        }

        // For all other routes (not under /admin/tools), do nothing
        return $next($request);
    }
}
