<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
       $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsNotSuspended::class,
        ]);

       $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'impersonate' => \App\Http\Middleware\ImpersonateUser::class,
            'admin.passkey' => \App\Http\Middleware\AdminPassKeyMiddleware::class,
            'track.agent.activity' => \App\Http\Middleware\TrackAgentActivity::class,
            'canonical.route' => \App\Http\Middleware\EnsureCanonicalRouteKey::class,
            'user.not_suspended' => \App\Http\Middleware\EnsureUserIsNotSuspended::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            // If debug is enabled, show Laravel's default error page
            if (config('app.debug')) {
                return null; // Let Laravel handle it with full debug info
            }

            // For production, show custom error pages
            if ($request->expectsJson()) {
                $statusCode = 500;
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $statusCode = $e->getStatusCode();
                }
                return response()->json([
                    'message' => 'An error occurred. Please try again later.',
                    'status' => $statusCode
                ], $statusCode);
            }

            // Handle specific HTTP exceptions
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->view('errors.404', [], 404);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $statusCode = $e->getStatusCode();

                switch ($statusCode) {
                    case 403:
                        return response()->view('errors.403', [], 403);
                    case 404:
                        return response()->view('errors.404', [], 404);
                    case 419:
                        return response()->view('errors.419', [], 419);
                    case 500:
                        return response()->view('errors.500', [], 500);
                    default:
                        return response()->view('errors.error', ['exception' => $e], $statusCode);
                }
            }

            // Handle general exceptions
            return response()->view('errors.500', [], 500);
        });
    })
    ->create();
