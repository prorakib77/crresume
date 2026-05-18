<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    // Show assignment form
    public function assignForm(Request $request)
    {
        $clients = User::where('role_id', 3)->with('clientProfile')->get();
        $agents = User::where('role_id', 2)->get();

        // If client ID is provided, get current assignments
        $currentClient = null;
        $currentAssignments = collect();

        if ($request->has('client') && $request->client) {
            $currentClient = User::with('clientProfile')->find($request->client);
            if ($currentClient && $currentClient->clientProfile) {
                $currentAssignments = User::whereHas('assignedClientProfiles', function($query) use ($currentClient) {
                    $query->where('client_profiles.id', $currentClient->clientProfile->id);
                })->where('role_id', 2)->get();
            }
        }

        return view('agents.asignIndex', compact('clients', 'agents', 'currentClient', 'currentAssignments'));
    }

    // Assign client to agent
    public function assign(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'agent_id'  => 'required|exists:users,id',
        ]);

        $client = User::with('clientProfile')->findOrFail($request->client_id);
        $agent = User::findOrFail($request->agent_id);

        // Verify client has profile and agent has correct role
        if (!$client->clientProfile) {
            return back()->withErrors(['client_id' => 'Selected client does not have a profile.']);
        }

        if ($agent->role_id != 2) {
            return back()->withErrors(['agent_id' => 'Selected user is not an agent.']);
        }

        if ($client->role_id != 3) {
            return back()->withErrors(['client_id' => 'Selected user is not a client.']);
        }

        // Check if assignment already exists
        $existingAssignment = DB::table('agent_client')
            ->where('client_id', $client->clientProfile->id)
            ->where('agent_id', $agent->id)
            ->first();

        if ($existingAssignment) {
            return back()->with('info', 'This agent is already assigned to this client.');
        }

        // Create new assignment
        DB::table('agent_client')->insert([
            'client_id' => $client->clientProfile->id,
            'agent_id' => $agent->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update client profile status to 'Assigned' (1)
        $client->clientProfile->update(['status' => 1]);

        return back()->with('success', "Agent '{$agent->name}' has been assigned to client '{$client->name}' successfully!");
    }

    // Remove assignment
    public function removeAssignment(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'agent_id'  => 'required|exists:users,id',
        ]);

        $client = User::with('clientProfile')->findOrFail($request->client_id);
        $agent = User::findOrFail($request->agent_id);

        if (!$client->clientProfile) {
            return back()->withErrors(['error' => 'Client profile not found.']);
        }

        // Remove assignment
        $deleted = DB::table('agent_client')
            ->where('client_id', $client->clientProfile->id)
            ->where('agent_id', $agent->id)
            ->delete();

        if ($deleted) {
            // Check if client has any remaining assignments
            $remainingAssignments = DB::table('agent_client')
                ->where('client_id', $client->clientProfile->id)
                ->count();

            // If no more assignments, set status back to inactive
            if ($remainingAssignments == 0) {
                $client->clientProfile->update(['status' => 0]);
            }

            return back()->with('success', "Agent '{$agent->name}' has been unassigned from client '{$client->name}' successfully!");
        }

        return back()->with('error', 'Assignment not found.');
    }

    // Show all assignments (index view)
    public function index()
    {
        $agents = User::where('role_id', 2)->get();

        // Fetch clients with their assigned agents and client profiles
        $clients = User::where('role_id', 3)
                    ->with(['clientProfile'])
                    ->get()
                    ->map(function($client) {
                        // Get assigned agents for this client
                        $client->assignedAgents = collect();
                        if ($client->clientProfile) {
                            $client->assignedAgents = User::whereHas('assignedClientProfiles', function($query) use ($client) {
                                $query->where('client_profiles.id', $client->clientProfile->id);
                            })->where('role_id', 2)->get();
                        }
                        return $client;
                    });

        return view('agents.agent_assign', compact('agents', 'clients'));
    }
}
