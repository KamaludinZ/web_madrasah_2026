<?php

namespace App\Filament\Pages\Auth;

use App\Mail\LoginOtpMail;
use App\Models\Setting;
use App\Models\User;
use App\Support\MailSettings;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

/**
 * Satu-satunya halaman login panel admin.
 *
 * Alur: email + password + kode keamanan → (jika OTP aktif di Pengaturan
 * Website → Keamanan) kode OTP 6 digit dikirim ke email pengguna → login.
 * Sebelumnya OTP hanya diperiksa di /login terpisah, sehingga /admin/login
 * bisa dipakai untuk melewati OTP sepenuhnya.
 */
class Login extends BaseLogin
{
    /** Maksimal salah memasukkan OTP sebelum kode dibatalkan. */
    private const MAX_OTP_ATTEMPTS = 5;

    protected static string $view = 'filament.pages.auth.login';

    /** true = sedang di langkah memasukkan kode OTP. */
    #[Locked]
    public bool $otpStep = false;

    #[Locked]
    public ?int $otpUserId = null;

    #[Locked]
    public bool $otpRemember = false;

    // ─── Kode keamanan ───────────────────────────────────────────────────────
    // Kode disimpan di session server dan ditampilkan sebagai GAMBAR
    // (App\Support\NumericCaptcha). Sebelumnya kode dikirim ke browser sebagai
    // teks/properti Livewire sehingga mudah dibaca bot.

    /** Minta browser memuat gambar kode baru & kosongkan isian. */
    private function newCaptcha(): void
    {
        $this->data['captcha'] = '';
        $this->dispatch('captcha-reload');
    }

    // ─── Form ────────────────────────────────────────────────────────────────

    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getEmailFormComponent()->hidden(fn () => $this->otpStep),
            $this->getPasswordFormComponent()->hidden(fn () => $this->otpStep),
            $this->getRememberFormComponent()->hidden(fn () => $this->otpStep),
            $this->getCaptchaFormComponent()->hidden(fn () => $this->otpStep),
            $this->getOtpFormComponent()->visible(fn () => $this->otpStep),
        ])->statePath('data');
    }

    protected function getCaptchaFormComponent(): TextInput
    {
        return TextInput::make('captcha')
            ->label('Kode Keamanan')
            ->helperText('Ketik angka yang tampil pada gambar di bawah.')
            ->placeholder('Ketik kode keamanan')
            ->required()
            ->maxLength(\App\Support\NumericCaptcha::LENGTH)
            ->autocomplete('off')
            ->extraInputAttributes([
                'inputmode'    => 'numeric',
                'class'        => 'tracking-widest font-mono text-center text-lg',
                'autocorrect'  => 'off',
                'spellcheck'   => 'false',
            ]);
    }

    protected function getOtpFormComponent(): TextInput
    {
        return TextInput::make('otp')
            ->label('Kode OTP')
            ->helperText('Kode 6 digit telah dikirim ke email akun Anda.')
            ->placeholder('Masukkan 6 digit kode')
            ->required()
            ->length(6)
            ->autocomplete('one-time-code')
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'class'     => 'tracking-widest font-mono text-center text-lg',
            ]);
    }

    // ─── Authenticate ────────────────────────────────────────────────────────

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        if ($this->otpStep) {
            return $this->verifyOtp();
        }

        // Validasi kode keamanan (sekali pakai — gambar selalu diganti setelah dicek)
        if (! \App\Support\NumericCaptcha::check('login', $this->data['captcha'] ?? null)) {
            $this->newCaptcha();

            throw ValidationException::withMessages([
                'data.captcha' => 'Kode keamanan tidak sesuai. Silakan coba lagi.',
            ]);
        }

        try {
            $data = $this->form->getState();
        } catch (ValidationException $e) {
            $this->newCaptcha(); // kode sudah terpakai — tampilkan gambar baru
            throw $e;
        }

        $credentials = $this->getCredentialsFromFormData($data);
        $guard = Filament::auth();

        // Cek kredensial TANPA login dulu — login baru dilakukan setelah OTP
        // (jika OTP aktif) supaya sesi tidak pernah terbentuk sebelum lolos 2FA.
        if (! $guard->validate($credentials)) {
            $this->newCaptcha();
            $this->throwFailureValidationException();
        }

        /** @var User $user */
        $user = $guard->getProvider()->retrieveByCredentials($credentials);

        if (($user instanceof FilamentUser) && ! $user->canAccessPanel(Filament::getCurrentPanel())) {
            $this->newCaptcha();
            $this->throwFailureValidationException();
        }

        if (! (bool) Setting::get('login_otp_enabled', false)) {
            return $this->completeLogin($user, (bool) ($data['remember'] ?? false));
        }

        return $this->startOtpStep($user, (bool) ($data['remember'] ?? false));
    }

    private function startOtpStep(User $user, bool $remember): ?LoginResponse
    {
        $otp = $user->generateOtp();
        $expiry = (int) config('auth.otp_expiry', 5);

        try {
            MailSettings::apply();
            Mail::to($user->email)->send(new LoginOtpMail($otp, $expiry));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim OTP login', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();

            Notification::make()
                ->title('Kode OTP gagal dikirim')
                ->body('Email tidak dapat dikirim. Periksa pengaturan email (SMTP) atau hubungi administrator.')
                ->danger()
                ->send();

            return null;
        }

        Cache::forget($this->otpAttemptsKey($user->id));

        $this->otpUserId = $user->id;
        $this->otpRemember = $remember;
        $this->otpStep = true;
        $this->data['password'] = null;
        $this->data['otp'] = null;

        Notification::make()
            ->title('Kode OTP telah dikirim')
            ->body('Cek kotak masuk email ' . $user->email . ' (termasuk folder spam).')
            ->success()
            ->send();

        return null;
    }

    private function verifyOtp(): ?LoginResponse
    {
        $user = $this->otpUserId ? User::find($this->otpUserId) : null;

        if (! $user) {
            $this->cancelOtp();
            return null;
        }

        $attemptsKey = $this->otpAttemptsKey($user->id);
        $code = preg_replace('/\D/', '', (string) ($this->data['otp'] ?? ''));

        if (! $user->verifyOtp($code)) {
            $attempts = (int) Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addMinutes(15));

            if ($attempts >= self::MAX_OTP_ATTEMPTS) {
                $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();
                $this->cancelOtp();

                Notification::make()
                    ->title('Terlalu banyak kode salah')
                    ->body('Kode OTP dibatalkan. Silakan login ulang untuk mendapatkan kode baru.')
                    ->danger()
                    ->send();

                return null;
            }

            throw ValidationException::withMessages([
                'data.otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        Cache::forget($attemptsKey);
        $user->forceFill(['otp_code' => null, 'otp_expires_at' => null])->save();

        return $this->completeLogin($user, $this->otpRemember);
    }

    private function completeLogin(User $user, bool $remember): LoginResponse
    {
        Filament::auth()->login($user, $remember);
        session()->regenerate();

        return app(LoginResponse::class);
    }

    /** Kembali ke langkah email + password (dipanggil dari tombol di view). */
    public function cancelOtp(): void
    {
        $this->otpStep = false;
        $this->otpUserId = null;
        $this->otpRemember = false;
        $this->data['otp'] = null;
        $this->data['captcha'] = '';
        $this->newCaptcha();
    }

    private function otpAttemptsKey(int $userId): string
    {
        return "login-otp-attempts:{$userId}";
    }
}
