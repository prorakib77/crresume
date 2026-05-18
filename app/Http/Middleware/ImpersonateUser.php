<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('impersonate_user_id')) {
            $originalUserId = Auth::id();
            $impersonateUserId = $request->get('impersonate_user_id');

            // Only Super Admin and Admin can impersonate
            if (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()) {
                // Store original user ID in session
                session(['impersonating' => $originalUserId]);

                // Login as the impersonated user
                Auth::loginUsingId($impersonateUserId);
            }
        }

        return $next($request);
    }
}
