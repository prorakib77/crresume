<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class AdminPassKeyController extends Controller
{
    /**
     * Show the admin pass key form
     */
    public function showForm()
    {
        return view('admin.passkey-form');
    }

    /**
     * Verify the admin pass key
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pass_key' => 'required|string'
        ]);

        $enteredPassKey = $request->input('pass_key');
        $correctPassKey = config('app.admin_pass_key', 'admin123'); // Default pass key

        if (Hash::check($enteredPassKey, Hash::make($correctPassKey)) || $enteredPassKey === $correctPassKey) {
            // Set session to indicate pass key is verified
            Session::put('admin_pass_key_verified', true);
            Session::put('admin_pass_key_verified_at', now());

            return redirect()->route('admin.dashboard')
                           ->with('success', 'Admin access granted! You now have temporary admin privileges.');
        }

        return redirect()->back()
                       ->with('error', 'Invalid pass key. Please try again.')
                       ->withInput();
    }

    /**
     * Revoke admin pass key access
     */
    public function revoke()
    {
        Session::forget('admin_pass_key_verified');
        Session::forget('admin_pass_key_verified_at');

        return redirect()->route('dashboard')
                       ->with('info', 'Admin access revoked. You have been returned to your regular dashboard.');
    }

    /**
     * Check if user has admin pass key access
     */
    public function checkAccess()
    {
        if (Session::has('admin_pass_key_verified')) {
            $verifiedAt = Session::get('admin_pass_key_verified_at');
            $hoursSinceVerification = now()->diffInHours($verifiedAt);

            // Pass key expires after 24 hours
            if ($hoursSinceVerification > 24) {
                Session::forget('admin_pass_key_verified');
                Session::forget('admin_pass_key_verified_at');
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Show pass key change form (admin only)
     */
    public function showChangeForm()
    {
        return view('admin.passkey-change');
    }

    /**
     * Update the admin pass key (admin only)
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_pass_key' => 'required|string',
            'new_pass_key' => 'required|string|min:6|confirmed',
            'new_pass_key_confirmation' => 'required|string'
        ]);

        $currentPassKey = config('app.admin_pass_key', 'admin123');
        $enteredCurrentPassKey = $request->input('current_pass_key');

        // Verify current pass key
        if ($enteredCurrentPassKey !== $currentPassKey) {
            return redirect()->back()
                           ->with('error', 'Current pass key is incorrect.')
                           ->withInput();
        }

        // Update the pass key in environment file
        $this->updateEnvironmentFile('ADMIN_PASS_KEY', $request->input('new_pass_key'));

        // Clear any existing pass key sessions to force re-verification
        Session::forget('admin_pass_key_verified');
        Session::forget('admin_pass_key_verified_at');

        return redirect()->route('admin.dashboard')
                       ->with('success', 'Admin pass key has been updated successfully. All users will need to use the new pass key.');
    }

    /**
     * Update environment file with new pass key
     */
    private function updateEnvironmentFile($key, $value)
    {
        $envFile = base_path('.env');

        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);

            // Check if key exists
            if (strpos($content, $key . '=') !== false) {
                // Update existing key
                $content = preg_replace(
                    '/^' . preg_quote($key) . '=.*/m',
                    $key . '=' . $value,
                    $content
                );
            } else {
                // Add new key
                $content .= "\n" . $key . '=' . $value;
            }

            file_put_contents($envFile, $content);
        }
    }
}
