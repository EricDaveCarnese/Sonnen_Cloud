<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog; // <--- ADDED IMPORT
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Roles that can be self-registered via the landing page.
     */
    private const PUBLIC_ROLES = ['owner_manager', 'admin', 'operations'];

    public function showLanding()
    {
        if (Auth::check()) {
            return redirect()->route('reports.dashboard');
        }

        // Determine which roles are still available for public registration
        $availableRoles = [];
        foreach (self::PUBLIC_ROLES as $role) {
            if (! User::where('role', $role)->exists()) {
                $availableRoles[] = $role;
            }
        }

        $registrationOpen = count($availableRoles) > 0;

        return view('landing', compact('availableRoles', 'registrationOpen'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Log successful login
            ActivityLog::record('Logged In', 'User logged into the system.');
            
            return redirect()->intended(route('reports.dashboard'));
        }

        return back()->withErrors(['username' => 'Invalid system credentials provided.']);
    }

    public function register(Request $request)
    {
        $fields = $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:100',
            'role'     => 'required|in:owner_manager,admin,operations',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Lockout check: reject if this role has already been publicly registered
        if (User::where('role', $fields['role'])->exists()) {
            return back()->withErrors([
                'role' => 'This role has already been registered. Public registration for this role is closed.',
            ])->withInput();
        }

        User::create([
            'fullname' => $fields['fullname'],
            'username' => $fields['username'],
            'role'     => $fields['role'],
            'password' => Hash::make($fields['password']),
            'status'   => 'active',
        ]);

        return redirect()->route('landing')->with('success', 'User account registered successfully. You may now login.');
    }

    public function logout(Request $request)
    {
        // Log successful logout
        ActivityLog::record('Logged Out', 'User logged out of the system.');
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password matches
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        // Optional: Log the password change for security
        ActivityLog::record('Security', 'User changed their password.');

        return back()->with('success', 'Password updated successfully!');
    }
}