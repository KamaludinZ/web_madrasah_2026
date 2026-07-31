<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class Login extends BaseLogin
{
    /**
     * Gunakan view custom yang menampilkan kotak kode CAPTCHA.
     */
    protected static string $view = 'filament.pages.auth.login';

    /**
     * Kode CAPTCHA — public property Livewire agar reaktif di view.
     * #[Locked] mencegah user manipulasi nilai dari browser.
     */
    #[Locked]
    public string $captchaCode = '';

    // ─── Generate ────────────────────────────────────────────────────────────

    private function newCaptcha(): void
    {
        $this->captchaCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function mount(): void
    {
        parent::mount();
        $this->newCaptcha();
    }

    // ─── Refresh (wire:click dari view) ──────────────────────────────────────

    public function refreshCaptcha(): void
    {
        $this->newCaptcha();
        $this->data['captcha'] = '';
    }

    // ─── Form ────────────────────────────────────────────────────────────────

    public function form(Form $form): Form
    {
        return $form->schema([
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
            $this->getCaptchaFormComponent(),
        ])->statePath('data');
    }

    protected function getCaptchaFormComponent(): TextInput
    {
        return TextInput::make('captcha')
            ->label('Kode Keamanan')
            ->helperText('Salin kode yang tertera, lalu ketik di kolom ini.')
            ->placeholder('Ketik kode keamanan')
            ->required()
            ->maxLength(6)
            ->autocomplete('off')
            ->extraInputAttributes([
                'inputmode'    => 'numeric',
                'class'        => 'tracking-widest font-mono text-center text-lg',
                'autocorrect'  => 'off',
                'spellcheck'   => 'false',
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

        // Validasi CAPTCHA — bandingkan dengan public property (bukan session)
        $input = trim($this->data['captcha'] ?? '');

        if ($input === '' || $input !== $this->captchaCode) {
            $this->newCaptcha();
            $this->data['captcha'] = '';

            throw ValidationException::withMessages([
                'data.captcha' => 'Kode keamanan tidak sesuai. Silakan coba lagi.',
            ]);
        }

        return parent::authenticate();
    }
}
