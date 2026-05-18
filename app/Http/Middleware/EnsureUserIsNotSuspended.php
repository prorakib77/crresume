<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $suspendedRouteName = 'account.suspended';

        if (($user->status ?? User::STATUS_ACTIVE) !== User::STATUS_SUSPENDED) {
            if ($request->routeIs($suspendedRouteName)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        }

        if ($request->routeIs($suspendedRouteName, 'logout')) {
            return $next($request);
        }

        if (
            $request->expectsJson()
            || $request->wantsJson()
            || $request->ajax()
            || $request->is('livewire/*')
            || $request->headers->has('X-Livewire')
        ) {
            return response()->json([
                'message' => 'User Suspended',
                'error' => 'user_suspended',
                'redirect_to' => route($suspendedRouteName),
            ], 423);
        }

        return redirect()->route($suspendedRouteName);
    }
}
