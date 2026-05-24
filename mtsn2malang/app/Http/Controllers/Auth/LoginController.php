<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Generate OTP for 2FA
            $user = Auth::user();
            $otp = $user->generateOtp();

            // Send OTP via email (in production)
            // Mail::to($user->email)->send(new OtpMail($otp));

            // For development, store in session
            session(['otp_user_id' => $user->id, 'otp_email' => $user->email]);

            Auth::logout();

            return redirect()->route('login')->with([
                'otp_sent' => true,
                'otp_email' => $user->email,
                'dev_otp' => config('app.debug') ? $otp : null, // Only show in debug mode
            ]);
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|array|size:6',
        ]);

        $otpCode = implode('', $request->otp);
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->verifyOtp($otpCode)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kadaluarsa.']);
        }

        // Clear OTP
        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
