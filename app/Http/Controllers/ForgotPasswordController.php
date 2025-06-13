<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash as FacadesHash;
use App\Models\User;
use App\Mail\OtpMail;

class ForgotPasswordController extends Controller
{
    public function showForgotForm() {
        return view('login.forgot-password');
    }

    public function sendResetOtp(Request $request) {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $otp = rand(100000, 999999);

        Session::put('otp', $otp);
        Session::put('email', $request->email);

        Mail::to($request->email)->send(new OtpMail($otp));

        return redirect()->route('password.otp')->with('success', 'OTP sent to your email.');
    }

    public function showOtpForm() {
        return view('login.verify-otp');
    }

    public function verifyOtp(Request $request) {
        $request->validate(['otp' => 'required|numeric']);
        if ($request->otp == Session::get('otp')) {
            return redirect()->route('password.reset');
        }

        return back()->withErrors(['otp' => 'Invalid OTP.']);
    }

    public function showResetForm() {
        return view('login.reset-password');
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::where('email', Session::get('email'))->first();
        if ($user) {
            $user->password = FacadesHash::make($request->password);
            $user->save();
            Session::forget(['otp', 'email']);
            return redirect()->route('login')->with('success', 'Password updated successfully.');
        }
        return back()->withErrors(['email' => 'User not found.']);
    }
}
