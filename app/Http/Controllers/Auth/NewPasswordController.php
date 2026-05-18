<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $resetUser = null;
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request, &$resetUser) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $resetUser = $user;
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $user = $resetUser;

            if ($user) {
                try {
                    $service = app(EmailTemplateService::class);
                    $fallbackBody = view('emails.password-reset-notification', [
                        'user' => $user,
                    ])->render();

                    $service->sendTemplate(
                        EmailTemplate::KEY_PASSWORD_RESET_CONFIRMATION,
                        (string) $user->email,
                        (string) $user->name,
                        [
                            'user_name' => $user->name,
                            'user_email' => $user->email,
                            'changed_at' => now()->format('M d, Y h:i A'),
                            'ip_address' => $request->ip() ?: 'Unknown',
                            'login_url' => route('login'),
                        ],
                        [
                            'subject_fallback' => 'Password Reset Successful',
                            'body_fallback' => $fallbackBody,
                        ]
                    );
                } catch (\Throwable $exception) {
                    Log::warning('Password reset confirmation email failed', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
