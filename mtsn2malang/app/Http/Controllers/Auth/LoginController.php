<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // ─── Helpers ────────────────────────────────────────────────────────────

    /** Generate 6-digit numeric CAPTCHA, store in session, return the code. */
    private function generateCaptcha(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        session(['login_captcha' => $code]);
        return $code;
    }

    private function otpEnabled(): bool
    {
        return (bool) Setting::get('login_otp_enabled', false);
    }

    // ─── Show login page ─────────────────────────────────────────────────────

    public function showLogin(Request $request)
    {
        // Always regenerate CAPTCHA on fresh GET (not when returning from OTP step)
        if (! session('otp_sent')) {
            $this->generateCaptcha();
        }

        return view('auth.login', [
            'captcha'    => session('login_captcha'),
            'otpEnabled' => $this->otpEnabled(),
        ]);
    }

    // ─── Handle credentials + CAPTCHA ────────────────────────────────────────

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'captcha'  => 'required|string',
        ]);

        // Validate CAPTCHA
        $expectedCaptcha = session('login_captcha');
        if (! $expectedCaptcha || trim($request->captcha) !== $expectedCaptcha) {
            $this->generateCaptcha();
            return back()
                ->withErrors(['captcha' => 'Kode keamanan tidak sesuai. Silakan coba lagi.'])
                ->onlyInput('email');
        }

        // Validate credentials
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $this->generateCaptcha();
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // ── OTP mode ON ──────────────────────────────────────────────────────
        if ($this->otpEnabled()) {
            $otp = $user->generateOtp();

            // In production: Mail::to($user->email)->send(new OtpMail($otp));

            $request->session()->put('otp_user_id', $user->id);
            $request->session()->put('otp_email', $user->email);
            $request->session()->put('otp_remember', $request->boolean('remember'));

            Auth::logout();

            // Log OTP ke file log saja saat development — JANGAN kirim ke response/session
            if (config('app.debug')) {
                \Illuminate\Support\Facades\Log::debug('[DEV] OTP for ' . $user->email . ': ' . $otp);
            }

            return redirect()->route('login')->with([
                'otp_sent'  => true,
                'otp_email' => $user->email,
            ]);
        }

        // ── OTP mode OFF → login langsung ────────────────────────────────────
        session()->forget('login_captcha');
        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    // ─── Verify OTP ──────────────────────────────────────────────────────────

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|array|size:6',
        ]);

        $sessionUserId = $request->session()->get('otp_user_id');
        $sessionEmail  = $request->session()->get('otp_email');

        if (! $sessionUserId || ! $sessionEmail || $sessionEmail !== $request->email) {
            return redirect()->route('login')->withErrors([
                'otp' => 'Sesi OTP tidak ditemukan. Silakan login ulang.',
            ]);
        }

        $otpCode = implode('', $request->otp);
        $user    = User::find($sessionUserId);

        if (! $user || $user->email !== $request->email || ! $user->verifyOtp($otpCode)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kadaluarsa.']);
        }

        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        $remember = (bool) $request->session()->pull('otp_remember', false);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->forget(['otp_user_id', 'otp_email']);

        return redirect()->intended('/admin');
    }

    // ─── Refresh CAPTCHA (AJAX) ───────────────────────────────────────────────

    public function refreshCaptcha(Request $request)
    {
        $code = $this->generateCaptcha();
        return response()->json(['captcha' => $code]);
    }

    // ─── Logout ──────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
