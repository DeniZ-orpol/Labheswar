<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }
        
        // Check session-based auth (branch users)
        if (session('user_type') === 'branch' && session('branch_user_id')) {
            return $next($request);
        }
        
        // Not authenticated, redirect to login
        return redirect()->route('login')->with('error', 'Please login to access this page.');
    }
}
