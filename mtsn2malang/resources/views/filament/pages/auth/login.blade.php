@php
    $logo = rescue(fn () => \App\Models\Setting::get('logo'), null, report: false);
    $logoUrl = ($logo && file_exists(public_path('storage/' . $logo))) ? '/storage/' . $logo : null;

    // Sama dengan yang dipakai header/footer website (Pengaturan Website).
    $siteName = rescue(fn () => \App\Models\Setting::get('site_name'), null, report: false) ?: 'MTsN 2 Kota Malang';
    $siteTagline = rescue(fn () => \App\Models\Setting::get('site_tagline'), null, report: false);
    $siteAddress = rescue(fn () => \App\Models\Setting::get('top_header_address'), null, report: false);

    $inputClass = 'w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-800 dark:text-white outline-none transition-all';
@endphp

<div class="min-h-screen flex font-sans">
    {{-- Panel kiri --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-school-green-dark via-school-green to-school-orange relative overflow-hidden">
        <div class="absolute inset-0 bg-pattern opacity-10"></div>
        <div class="relative z-10 flex flex-col items-center justify-center w-full p-12 text-white text-center">
            <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-6 shadow-2xl overflow-hidden">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo {{ $siteName }}" class="w-20 h-20 object-contain drop-shadow-lg">
                @else
                    <svg viewBox="0 0 60 60" class="w-16 h-16" fill="none">
                        <circle cx="30" cy="30" r="28" fill="white" fill-opacity="0.3"/>
                        <text x="30" y="38" text-anchor="middle" fill="white" font-size="20" font-weight="bold" font-family="Poppins">M2</text>
                    </svg>
                @endif
            </div>
            <h1 class="text-3xl font-bold font-heading mb-3">{{ $siteName }}</h1>
            @if (filled($siteTagline))
                <p class="text-white/80 text-lg">{{ $siteTagline }}</p>
            @endif

            @if (filled($siteAddress))
                <div class="mt-12 text-white/60 text-xs">
                    {{ $siteAddress }}
                </div>
            @endif
        </div>

        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full"></div>
    </div>

    {{-- Panel kanan --}}
    <div class="flex-1 flex items-center justify-center p-6 bg-gray-50 dark:bg-gray-900">
        <div class="w-full max-w-md">
            {{-- Logo versi mobile --}}
            <div class="lg:hidden text-center mb-8">
                <div class="w-16 h-16 bg-school-green rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg overflow-hidden">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo {{ $siteName }}" class="w-14 h-14 object-contain">
                    @else
                        <svg viewBox="0 0 60 60" class="w-12 h-12" fill="none">
                            <text x="30" y="38" text-anchor="middle" fill="white" font-size="18" font-weight="bold" font-family="Poppins">M2</text>
                        </svg>
                    @endif
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white font-heading">{{ $siteName }}</h1>
            </div>

            <form wire:submit="authenticate" novalidate>
                @if (! $this->otpStep)
                    {{-- Langkah 1: email + password + kode keamanan --}}
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white font-heading">Selamat Datang</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Masuk ke panel pengelolaan website</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                            <input type="email" id="email" wire:model="data.email"
                                   class="{{ $inputClass }} @error('data.email') border-red-400 @enderror"
                                   placeholder="email@mtsn2kotamalang.sch.id"
                                   autocomplete="username" required autofocus>
                            @error('data.email')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                            <div class="relative" x-data="{ show: false }">
                                <input x-bind:type="show ? 'text' : 'password'" type="password" id="password" wire:model="data.password"
                                       class="{{ $inputClass }} pr-12 @error('data.password') border-red-400 @enderror"
                                       placeholder="••••••••"
                                       autocomplete="current-password" required>
                                <button type="button" x-on:click="show = !show"
                                        x-bind:aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            @error('data.password')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kode keamanan (gambar angka) --}}
                        <div>
                            <label for="captcha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kode Keamanan</label>
                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                                <x-captcha-image context="login" class="flex-shrink-0 flex items-center gap-1" />
                                <input type="text" id="captcha" wire:model="data.captcha"
                                       inputmode="numeric" maxlength="{{ \App\Support\NumericCaptcha::LENGTH }}"
                                       autocomplete="off" autocorrect="off" spellcheck="false"
                                       placeholder="Ketik kode"
                                       class="{{ $inputClass }} min-w-0 basis-full sm:basis-0 sm:flex-1 tracking-widest font-mono @error('data.captcha') border-red-400 @enderror">
                            </div>
                            @error('data.captcha')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="data.remember" class="w-4 h-4 rounded border-gray-300 text-school-green focus:ring-green-200">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Ingat saya</span>
                        </label>
                    </div>
                @else
                    {{-- Langkah 2: kode OTP --}}
                    <div class="mb-8 text-center">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white font-heading">Verifikasi OTP</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Masukkan kode 6 digit yang dikirim ke email Anda</p>
                    </div>

                    <div>
                        <label for="otp" class="sr-only">Kode OTP</label>
                        <input type="text" id="otp" wire:model="data.otp"
                               inputmode="numeric" maxlength="6" autocomplete="one-time-code"
                               placeholder="••••••"
                               class="{{ $inputClass }} text-center text-2xl font-bold tracking-[0.5em] font-mono @error('data.otp') border-red-400 @enderror"
                               required autofocus>
                        @error('data.otp')
                            <p class="mt-1 text-xs text-red-500 text-center">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <button type="submit" wire:loading.attr="disabled" wire:target="authenticate"
                        class="mt-6 w-full py-3 bg-school-green text-white font-semibold rounded-xl hover:bg-school-green-dark transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg wire:loading wire:target="authenticate" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span wire:loading.remove wire:target="authenticate">{{ $this->otpStep ? 'Verifikasi' : 'Masuk' }}</span>
                    <span wire:loading wire:target="authenticate">Memproses...</span>
                </button>

                @if ($this->otpStep)
                    <div class="mt-4 text-center">
                        <button type="button" wire:click="cancelOtp" class="text-sm text-gray-500 hover:text-school-green">
                            ← Kembali / login dengan akun lain
                        </button>
                    </div>
                @endif
            </form>

            <div class="mt-8 text-center">
                <a href="{{ route('home') }}" class="text-sm text-gray-400 hover:text-school-green transition-colors inline-flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Website
                </a>
            </div>
        </div>
    </div>
</div>
