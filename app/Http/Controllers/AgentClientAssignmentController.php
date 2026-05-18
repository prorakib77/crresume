<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AgentClientAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentClientAssignmentController extends Controller
{
    /**
     * Display a listing of the assignments.
     */
    public function index()
    {
        $assignments = AgentClientAssignment::with(['agent', 'client'])
                                          ->latest()
                                          ->paginate(15);

        return view('agent-client-assignments.index', compact('assignments'));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $agents = User::whereHas('role', function($query) {
            $query->where('name', 'agent');
        })->where('status', 'active')->get();

        $clients = User::whereHas('role', function($query) {
            $query->where('name', 'client');
        })->where('status', 'active')->get();

        return view('agent-client-assignments.create', compact('agents', 'clients'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'service_end_date' => 'nullable|date|after:assigned_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if assignment already exists
        $existingAssignment = AgentClientAssignment::where('agent_id', $request->agent_id)
                                                 ->where('client_id', $request->client_id)
                                                 ->where('is_active', true)
                                                 ->first();

        if ($existingAssignment) {
            return back()->with('error', 'This agent is already assigned to this client.');
        }

        AgentClientAssignment::create([
            'agent_id' => $request->agent_id,
            'client_id' => $request->client_id,
            'assigned_date' => $request->assigned_date,
            'service_end_date' => $request->service_end_date,
            'is_active' => true,
            'notes' => $request->notes,
        ]);

        return redirect()->route('agent-client-assignments.index')
                        ->with('success', 'Agent assigned to client successfully!');
    }

    /**
     * Display the specified assignment.
     */
    public function show(AgentClientAssignment $assignment)
    {
        return view('agent-client-assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(AgentClientAssignment $assignment)
    {
        $agents = User::whereHas('role', function($query) {
            $query->where('name', 'agent');
        })->where('status', 'active')->get();

        $clients = User::whereHas('role', function($query) {
            $query->where('name', 'client');
        })->where('status', 'active')->get();

        return view('agent-client-assignments.edit', compact('assignment', 'agents', 'clients'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(Request $request, AgentClientAssignment $assignment)
    {
        $request->validate([
            'service_end_date' => 'nullable|date|after:assigned_date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignment->update([
            'service_end_date' => $request->service_end_date,
            'is_active' => $request->boolean('is_active'),
            'notes' => $request->notes,
        ]);

        return redirect()->route('agent-client-assignments.index')
                        ->with('success', 'Assignment updated successfully!');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(AgentClientAssignment $assignment)
    {
        $assignment->update(['is_active' => false]);

        return back()->with('success', 'Assignment deactivated successfully!');
    }

    /**
     * Show assignments for a specific agent.
     */
    public function showAgent(User $agent)
    {
        $assignments = $agent->agentAssignments()->with('client')->active()->get();

        return view('agent-client-assignments.agent', compact('agent', 'assignments'));
    }

    /**
     * Show assignments for a specific client.
     */
    public function showClient(User $client)
    {
        $assignments = $client->clientAssignments()->with('agent')->active()->get();

        return view('agent-client-assignments.client', compact('client', 'assignments'));
    }
}
