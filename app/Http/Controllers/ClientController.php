<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(){
        $user = auth()->user();

        if ($user->isAgent()) {
            // For agents, show only their assigned clients
            $clients = $user->clients()->with('clientProfile')->get();
            return view('clients.agent_clients', compact('clients'));
        } else {
            // For admins, show all clients
            $clients = ClientProfile::with('user')->get();
            return view('clients.index', compact('clients'));
        }
    }
    public function create()
{
    return view('clients.create');
}

    // Create new client
public function store(Request $request)
{
    $validated = $request->validate([
        'name'   => 'required|string|max:255',
        'email'  => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'phone'  => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'apply_to' => 'nullable|string|max:255',
        'status' => 'nullable|string|max:50',
        'resume' => 'nullable|file|mimes:pdf,doc,docx',
        'onboarding_file' => 'nullable|file|mimes:pdf,xls,xlsx,doc,docx',
        'service_start_date' => 'nullable|date',
        'service_end_date'   => 'nullable|date',
    ]);
    // dd($validated, $request->all());

    // Create user with role = client
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role_id' => 3, // assuming 3 = client
    ]);

    // Handle file uploads (inside public/)
    $resumePath = null;
    $onboardingPath = null;

    if ($request->hasFile('resume')) {
        $resumeDir = public_path('uploads/clients/resume');
        if (!file_exists($resumeDir)) {
            mkdir($resumeDir, 0777, true);
        }
        $resumeFile = $request->file('resume');
        $resumeName = time() . '_' . $resumeFile->getClientOriginalName();
        $resumeFile->move($resumeDir, $resumeName);
        $resumePath = 'uploads/clients/resume/' . $resumeName;
    }

    if ($request->hasFile('onboarding_file')) {
        $onboardingDir = public_path('uploads/clients/onboarding');
        if (!file_exists($onboardingDir)) {
            mkdir($onboardingDir, 0777, true);
        }
        $onboardingFile = $request->file('onboarding_file');
        $onboardingName = time() . '_' . $onboardingFile->getClientOriginalName();
        $onboardingFile->move($onboardingDir, $onboardingName);
        $onboardingPath = 'uploads/clients/onboarding/' . $onboardingName;
    }

    // Create client profile
    ClientProfile::create([
        'user_id' => $user->id,
        'phone' => $validated['phone'] ?? null,
        'address' => $validated['address'] ?? null,
        'apply_to' => $validated['apply_to'] ?? null,
        'status' => $validated['status'] ?? '0',
        'resume' => $resumePath,
        'onboarding_file' => $onboardingPath,
        'service_start_date' => $validated['service_start_date'] ?? null,
        'service_end_date' => $validated['service_end_date'] ?? null,
        'onboarding_visible' => true,
        'onboarding_status' => ClientProfile::ONBOARDING_STATUS_PENDING,
        'service_type' => ClientProfile::SERVICE_TYPE_REGULAR,
    ]);

    return redirect()->back()->with('success', 'Client created successfully!');
}

}
