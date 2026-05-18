<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckInController extends Controller
{
    /**
     * Show check-in/check-out status
     */
    public function index()
    {
        $agent = Auth::user();

        // Get today's check-in/check-out activities
        $todayActivities = AgentActivity::where('agent_id', $agent->id)
            ->whereIn('activity_type', ['check_in', 'check_out'])
            ->whereDate('activity_time', today())
            ->orderBy('activity_time', 'desc')
            ->get();

        // Check if currently checked in
        $isCheckedIn = $this->isCurrentlyCheckedIn($agent->id);

        // Get work hours for today
        $workHours = AgentActivity::getTodayWorkHours($agent->id);

        return view('agent.checkin.index', compact('todayActivities', 'isCheckedIn', 'workHours'));
    }

    /**
     * Check in agent
     */
    public function checkIn(Request $request)
    {
        $agent = Auth::user();

        // Check if already checked in
        if ($this->isCurrentlyCheckedIn($agent->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already checked in!'
            ]);
        }

        // Create check-in activity
        AgentActivity::create([
            'agent_id' => $agent->id,
            'activity_type' => 'check_in',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'activity_time' => now(),
            'additional_data' => [
                'location' => $request->get('location', 'Unknown'),
                'notes' => $request->get('notes', ''),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully checked in!',
            'check_in_time' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Check out agent
     */
    public function checkOut(Request $request)
    {
        $agent = Auth::user();

        // Check if currently checked in
        if (!$this->isCurrentlyCheckedIn($agent->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not checked in!'
            ]);
        }

        // Create check-out activity
        AgentActivity::create([
            'agent_id' => $agent->id,
            'activity_type' => 'check_out',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'activity_time' => now(),
            'additional_data' => [
                'location' => $request->get('location', 'Unknown'),
                'notes' => $request->get('notes', ''),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully checked out!',
            'check_out_time' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Get check-in/check-out status
     */
    public function getStatus()
    {
        $agent = Auth::user();
        $isCheckedIn = $this->isCurrentlyCheckedIn($agent->id);
        $workHours = AgentActivity::getTodayWorkHours($agent->id);

        return response()->json([
            'is_checked_in' => $isCheckedIn,
            'work_hours' => $workHours,
        ]);
    }

    /**
     * Check if agent is currently checked in
     */
    private function isCurrentlyCheckedIn($agentId)
    {
        $lastCheckIn = AgentActivity::where('agent_id', $agentId)
            ->where('activity_type', 'check_in')
            ->whereDate('activity_time', today())
            ->orderBy('activity_time', 'desc')
            ->first();

        $lastCheckOut = AgentActivity::where('agent_id', $agentId)
            ->where('activity_type', 'check_out')
            ->whereDate('activity_time', today())
            ->orderBy('activity_time', 'desc')
            ->first();

        if (!$lastCheckIn) {
            return false;
        }

        if (!$lastCheckOut) {
            return true;
        }

        return $lastCheckIn->activity_time > $lastCheckOut->activity_time;
    }
}
