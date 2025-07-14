<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchUsers;
use App\Models\Role;
use App\Models\User;
use Cookie;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // if (Auth::check() || session('user_type') === 'branch') {
        //     return redirect()->route('dashboard');
        // }
        return view('login.login'); // Create this view in resources/views/auth/login.blade.php
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;
        $remember = $request->filled('remember');

        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $user = Auth::user();

            // Store user type in session
            // session([
            //     'user_type' => 'master',
            //     'user_role' => $user->role,
            //     'user_name' => $user->name,
            //     'user_email' => $user->email,
            // ]);

            return redirect()->route('dashboard')->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // $branches = Branch::all();

        // foreach ($branches as $branch) {
        //     try {
        //         $branchUser = BranchUsers::forDatabase($branch->getDatabaseName())
        //             ->where('email', $email)
        //             ->where('is_active', true)
        //             ->first();

        //         if ($branchUser) {
        //             if (Hash::check($password, $branchUser->password)) {
        //                 // if ($branchUser && password_verify($password, $branchUser->password)) {
        //                 // Get role info
        //                 $role = Role::forDatabase($branch->getDatabaseName())->find($branchUser->role_id);

        //                 // Handle remember me for branch users
        //                 if ($remember) {
        //                     $rememberToken = Str::random(60);
        //                     $branchUser->update([
        //                         'remember_token' => hash('sha256', data: $rememberToken),
        //                         'last_login_at' => now()
        //                     ]);

        //                     // Set remember me cookie for branch user
        //                     Cookie::queue('branch_remember_token', $rememberToken, 43200); // 30 days
        //                     Cookie::queue('branch_user_id', $branchUser->id, 43200);
        //                     Cookie::queue('branch_connection', $branch->connection_name, 43200);
        //                 } else {
        //                     // Update last login
        //                     $branchUser->update(['last_login_at' => now()]);
        //                 }
        //                 // Store branch user info in session
        //                 session([
        //                     'user_type' => 'branch',
        //                     'branch_user_id' => $branchUser->id,
        //                     'branch_id' => $branch->id,
        //                     'branch_name' => $branch->name,
        //                     'branch_connection' => $branch->connection_name,
        //                     'user_role' => $role ? $role->role_name : null,
        //                     'user_name' => $branchUser->name,
        //                     'user_email' => $branchUser->email,
        //                 ]);

        //                 return redirect()->route('dashboard')->with('success', 'Welcome back, ' . $branchUser->name . '!');

        //             } else {
        //                 dd('password incorrect');
        //             }
        //         }
        //     } catch (Exception $e) {
        //         dd($e->getMessage());
        //         \Log::warning("Login attempt failed for branch {$branch->name}: " . $e->getMessage());
        //         continue;
        //     }
        // }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }


    // public function logout(Request $request)
    // {
    //     // Manually remove remember token from DB
    //     $user = Auth::user();
    //     if ($user) {
    //         $user->setRememberToken(null);
    //         $user->save();
    //     }

    //     // Logout the user
    //     Auth::logout();

    //     // Invalidate the session and regenerate CSRF token
    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return redirect()->route('login'); // or wherever you want to redirect
    // }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Clear remember token for branch users
        // if (session('user_type') === 'branch') {
        //     $branchConnection = session('branch_connection');
        //     $branchUserId = session('branch_user_id');

        //     if ($branchConnection && $branchUserId) {
        //         try {
        //             BranchUsers::forDatabase($branchConnection)
        //                 ->where('id', $branchUserId)
        //                 ->update(['remember_token' => null]);
        //         } catch (\Exception $e) {
        //             // Log error but continue logout
        //             \Log::error("Failed to clear remember token: " . $e->getMessage());
        //         }
        //     }

        //     // Clear branch remember cookies
        //     Cookie::queue(Cookie::forget('branch_remember_token'));
        //     Cookie::queue(Cookie::forget('branch_user_id'));
        //     Cookie::queue(Cookie::forget('branch_connection'));
        // }

        // Logout master user
        Auth::logout();

        // Clear all session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Get current authenticated user (helper method)
     */
    public function getCurrentUser()
    {
        if (session('user_type') === 'master') {
            return Auth::user();
        } elseif (session('user_type') === 'branch') {
            $branchConnection = session('branch_connection');
            $branchUserId = session('branch_user_id');

            if ($branchConnection && $branchUserId) {
                try {
                    return BranchUsers::forDatabase($branchConnection)->find($branchUserId);
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        $userRole = session('user_role');

        // Define permissions by role
        $rolePermissions = [
            'superadmin' => ['*'], // All permissions
            'branch_admin' => ['manage_users', 'view_reports', 'manage_inventory', 'manage_sales'],
            'manager' => ['view_reports', 'manage_inventory', 'manage_sales'],
            'employee' => ['manage_sales', 'view_inventory'],
            'cashier' => ['process_sales', 'handle_payments']
        ];

        if (isset($rolePermissions[$userRole])) {
            return in_array('*', $rolePermissions[$userRole]) || in_array($permission, $rolePermissions[$userRole]);
        }

        return false;
    }
}
