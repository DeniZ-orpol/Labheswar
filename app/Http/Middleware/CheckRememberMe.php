<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\BranchUsers;
use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CheckRememberMe
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip if user is already authenticated
        if (Auth::check() || session('user_type')) {
            return $next($request);
        }

        // Check for branch user remember me cookies
        $this->checkBranchRememberMe();

        return $next($request);
    }

    /**
     * Check and auto-login branch user from remember me cookies
     */
    private function checkBranchRememberMe()
    {
        $rememberToken = Cookie::get('branch_remember_token');
        $branchUserId = Cookie::get('branch_user_id');
        $branchConnection = Cookie::get('branch_connection');

        // If any cookie is missing, skip
        if (!$rememberToken || !$branchUserId || !$branchConnection) {
            return;
        }

        try {
            // Find the branch user
            $branchUser = BranchUsers::forDatabase($branchConnection)
                ->where('id', $branchUserId)
                ->where('is_active', true)
                ->first();

            // Verify the remember token
            if (!$branchUser || !hash_equals($branchUser->remember_token, hash('sha256', $rememberToken))) {
                $this->clearRememberCookies();
                return;
            }

            // Get branch information
            $branch = Branch::where('connection_name', $branchConnection)->first();

            if (!$branch) {
                $this->clearRememberCookies();
                return;
            }

            // Get role information
            $role = Role::forDatabase($branchConnection)->find($branchUser->role_id);

            // Auto login branch user by setting session
            session([
                'user_type' => 'branch',
                'branch_user_id' => $branchUser->id,
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'branch_connection' => $branch->connection_name,
                'user_role' => $role ? $role->role_name : null,
                'user_name' => $branchUser->name,
                'user_email' => $branchUser->email,
            ]);

            // Update last login time
            $branchUser->update(['last_login_at' => now()]);

        } catch (\Exception $e) {
            // If anything goes wrong, clear the cookies
            $this->clearRememberCookies();
            \Log::error("Remember me auto-login failed: " . $e->getMessage());
        }
    }

    /**
     * Clear remember me cookies
     */
    private function clearRememberCookies()
    {
        Cookie::queue(Cookie::forget('branch_remember_token'));
        Cookie::queue(Cookie::forget('branch_user_id'));
        Cookie::queue(Cookie::forget('branch_connection'));
    }
}
