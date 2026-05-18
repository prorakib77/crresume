<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentActivity;
use App\Models\AgentClientAssignment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgentController extends Controller
{
    /**
     * Display a listing of agents
     */
    public function index(Request $request)
    {
        return view('admin.agents.index');
    }

    /**
     * Show agent details and activity
     */
    public function show(User $agent)
    {
        // Ensure the user is an agent
        if (!$agent->hasRole('agent')) {
            abort(404);
        }

        $date = request('date', today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // Get activities for the selected date
        $activities = AgentActivity::where('agent_id', $agent->id)
            ->whereDate('activity_time', $selectedDate)
            ->orderBy('activity_time', 'desc')
            ->get();

        // Get work hours for the selected date
        $workHours = AgentActivity::getTodayWorkHours($agent->id, $selectedDate);

        // Get page visits
        $pageVisits = AgentActivity::getTodayPageVisits($agent->id, $selectedDate);

        // Get assigned clients
        $assignedClients = AgentClientAssignment::where('agent_id', $agent->id)
            ->with('client')
            ->newestFirst()
            ->get();

        return view('admin.agents.show', compact('agent', 'activities', 'workHours', 'pageVisits', 'assignedClients', 'selectedDate'));
    }

    /**
     * Get daily report for all agents
     */
    public function dailyReport(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $report = AgentActivity::getDailyReport($selectedDate);

        return view('admin.agents.daily-report', compact('report', 'selectedDate'));
    }

    /**
     * Get agent activity data for AJAX
     */
    public function getActivityData(User $agent, Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $activities = AgentActivity::where('agent_id', $agent->id)
            ->whereDate('activity_time', $selectedDate)
            ->orderBy('activity_time', 'desc')
            ->get();

        $workHours = AgentActivity::getTodayWorkHours($agent->id, $selectedDate);
        $pageVisits = AgentActivity::getTodayPageVisits($agent->id, $selectedDate);

        return response()->json([
            'activities' => $activities,
            'workHours' => $workHours,
            'pageVisits' => $pageVisits,
        ]);
    }
}
