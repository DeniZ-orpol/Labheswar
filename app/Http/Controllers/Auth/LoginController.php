<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Step 2: Attempt login manually
        $user = User::where('email', $request->email)->first();

        if (!$user || !\Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'Invalid email or password.',
            ], 401);
        }
        
        // Step 3: Login the user
        Auth::login($user);
        
        // Step 4: Regenerate session to avoid fixation
        dd($request->all());
        $request->session()->regenerate();

        // Step 5: Redirect or return success
        return view('pages/dashboard-overview-4');
        // return redirect()->route('/dashboard');
    }


    public function dashboardOverview4(): View
    {
        return view('pages/dashboard-overview-4');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
