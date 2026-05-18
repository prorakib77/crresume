<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClientProfile;
use App\Services\UserCommunicationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected UserCommunicationService $communicationService
    ) {
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => User::ROLE_CLIENT, // Set default role as client
            'status' => User::STATUS_ACTIVE,
        ]);

        // Create a basic client profile for the new user
        ClientProfile::create([
            'user_id' => $user->id,
            'status' => 0, // Default status
            'onboarding_visible' => true,
            'onboarding_status' => ClientProfile::ONBOARDING_STATUS_PENDING,
            'service_type' => ClientProfile::SERVICE_TYPE_REGULAR,
        ]);

        $this->communicationService->sendClientWelcomeEmail($user);

        event(new Registered($user));

        Auth::login($user);

        // Redirect to client dashboard instead of general dashboard
        return redirect(route('client.dashboard', absolute: false));
    }
}
