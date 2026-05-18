<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AgentActivity;
use Illuminate\Support\Facades\Auth;

class TrackAgentActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only track for authenticated agents
        if (Auth::check() && Auth::user()->hasRole('agent')) {
            $this->trackPageVisit($request);
        }

        return $response;
    }

    /**
     * Track page visit for agent
     */
    private function trackPageVisit(Request $request)
    {
        try {
            AgentActivity::create([
                'agent_id' => Auth::id(),
                'activity_type' => 'page_visit',
                'page_url' => $request->fullUrl(),
                'page_title' => $this->getPageTitle($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'activity_time' => now(),
                'additional_data' => [
                    'method' => $request->method(),
                    'route' => $request->route()?->getName(),
                ],
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the request
            \Log::error('Failed to track agent activity: ' . $e->getMessage());
        }
    }

    /**
     * Get page title from request
     */
    private function getPageTitle(Request $request)
    {
        $route = $request->route();
        if (!$route) return 'Unknown Page';

        $routeName = $route->getName();

        // Map route names to friendly titles
        $titles = [
            'agent.dashboard' => 'Agent Dashboard',
            'agent.work-updates.index' => 'Work Updates',
            'agent.work-updates.create' => 'Create Work Update',
            'agent.submissions.index' => 'Client Submissions',
            'agent.clients.index' => 'My Clients',
            'profile.edit' => 'Edit Profile',
        ];

        return $titles[$routeName] ?? ucwords(str_replace(['.', '-'], [' ', ' '], $routeName));
    }
}
