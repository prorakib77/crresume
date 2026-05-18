<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AdminUserLoginController extends Controller
{
    /**
     * Show the admin login as user form
     */
    public function showForm()
    {
        return view('admin.login-as-user');
    }

    /**
     * Login as user using admin pass key
     */
    public function loginAsUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'admin_pass_key' => 'required|string'
        ]);

        $email = $request->input('email');
        $adminPassKey = $request->input('admin_pass_key');
        $correctPassKey = config('app.admin_pass_key', 'admin123');

        // Verify admin pass key
        if ($adminPassKey !== $correctPassKey) {
            return redirect()->back()
                           ->with('error', 'Invalid admin pass key.')
                           ->withInput();
        }

        // Find the user
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->back()
                           ->with('error', 'User not found.')
                           ->withInput();
        }

        // Login as the user
        Auth::login($user);

        // Log the admin login action
        Log::info('Admin login as user', [
            'admin_email' => Auth::user()->email,
            'target_user' => $user->email,
            'action' => 'admin_login_as_user',
            'timestamp' => now()
        ]);

        return redirect()->route('dashboard')
                       ->with('success', 'Successfully logged in as ' . $user->name);
    }

    /**
     * Show user search form for admin login
     */
    public function showUserSearch()
    {
        return view('admin.user-search');
    }

    /**
     * Search users for admin login
     */
    public function searchUsers(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            return response()->json([]);
        }

        $users = User::where('email', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%')
                    ->limit(10)
                    ->get(['id', 'name', 'email', 'role_id']);

        return response()->json($users);
    }
}
