<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AdminPassKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                           ->with('error', 'You must be logged in to access this resource.');
        }

        $user = Auth::user();

        // Super admin and admin always have access
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user has admin pass key access
        if (Session::has('admin_pass_key_verified')) {
            return $next($request);
        }

        // Check if user is trying to access admin pass key verification
        if ($request->routeIs('admin.passkey.verify') || $request->routeIs('admin.passkey.form')) {
            return $next($request);
        }

        // Redirect to pass key verification
        return redirect()->route('admin.passkey.form')
                       ->with('info', 'Please enter the admin pass key to access admin features.');
    }
}
