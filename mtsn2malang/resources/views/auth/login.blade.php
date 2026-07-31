<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - MTsN 2 Kota Malang</title>
    @php
        $dynamicFavicon = \App\Models\Setting::get('favicon');
        if ($dynamicFavicon && file_exists(public_path('storage/' . $dynamicFavicon))) {
            $faviconRelPath = 'storage/' . $dynamicFavicon;
            $faviconExt = strtolower(pathinfo($dynamicFavicon, PATHINFO_EXTENSION));
        } else {
            $faviconRelPath = file_exists(public_path('images/favicon.ico'))
                ? 'images/favicon.ico'
                : 'images/favicon.png';
            $faviconExt = pathinfo($faviconRelPath, PATHINFO_EXTENSION);
        }
        $faviconVersion = file_exists(public_path($faviconRelPath)) ? filemtime(public_path($faviconRelPath)) : time();
        $faviconUrl = '/' . $faviconRelPath;
        $faviconMime = match($faviconExt) {
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'webp' => 'image/webp',
            default => 'image/x-icon',
        };
        $dynamicLogo = \App\Models\Setting::get('logo');
        $logoUrl = ($dynamicLogo && file_exists(public_path('storage/' . $dynamicLogo)))
            ? '/storage/' . $dynamicLogo
            : null;
    @endphp
    <link rel="icon" href="/images/favicon.ico" sizes="any">
    <link rel="icon" type="{{ $faviconMime }}" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
    <link rel="shortcut icon" type="{{ $faviconMime }}" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 font-sans">

<div class="min-h-screen flex">
    {{-- Left Panel --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-school-green-dark via-school-green to-school-orange relative overflow-hidden">
        <div class="absolute inset-0 bg-pattern opacity-10"></div>
        <div class="relative z-10 flex flex-col items-center justify-center p-12 text-white text-center">
            <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-6 shadow-2xl overflow-hidden">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo MTsN 2 Kota Malang" class="w-20 h-20 object-contain drop-shadow-lg">
                @else
                    <svg viewBox="0 0 60 60" class="w-16 h-16" fill="none">
                        <circle cx="30" cy="30" r="28" fill="white" fill-opacity="0.3"/>
                        <text x="30" y="38" text-anchor="middle" fill="white" font-size="20" font-weight="bold" font-family="Poppins">M2</text>
                    </svg>
                @endif
            </div>
            <h1 class="text-3xl font-bold font-heading mb-3">MTsN 2 Kota Malang</h1>
            <p class="text-white/80 text-lg mb-2">Madrasah Tsanawiyah Negeri 2</p>
            <p class="text-white/60 text-sm">Unggul, Berkarakter, dan Berwawasan Global</p>

            <div class="mt-12 grid grid-cols-3 gap-6 w-full max-w-sm">
                <div class="text-center">
                    <div class="text-2xl font-bold">1000+</div>
                    <div class="text-white/60 text-xs">Siswa</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">80+</div>
                    <div class="text-white/60 text-xs">Guru & Staff</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold">50+</div>
                    <div class="text-white/60 text-xs">Prestasi/Tahun</div>
                </div>
            </div>

            <div class="mt-12 text-white/40 text-xs">
                Jl. Raya Cemorokandang No. 77, Kota Malang 65138
            </div>
        </div>

        {{-- Decorative circles --}}
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full"></div>
    </div>

    {{-- Right Panel --}}
    <div class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            {{-- Mobile Logo --}}
            <div class="lg:hidden text-center mb-8">
                <div class="w-16 h-16 bg-school-green rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg overflow-hidden">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo MTsN 2 Kota Malang" class="w-14 h-14 object-contain">
                    @else
                        <svg viewBox="0 0 60 60" class="w-12 h-12" fill="none">
                            <text x="30" y="38" text-anchor="middle" fill="white" font-size="18" font-weight="bold" font-family="Poppins">M2</text>
                        </svg>
                    @endif
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white font-heading">MTsN 2 Kota Malang</h1>
            </div>

            <div x-data="{ step: '{{ session('otp_sent') ? 'otp' : 'credentials' }}', email: '{{ session('otp_email', '') }}', loading: false }">

                {{-- Step 1: Credentials --}}
                <div x-show="step === 'credentials'">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white font-heading">Selamat Datang</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Masuk ke panel pengelolaan website</p>
                    </div>

                    @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
                        {{ session('error') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" @submit="loading = true">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    class="w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-800 dark:text-white outline-none transition-all @error('email') border-red-400 @enderror"
                                    placeholder="email@mtsn2kotamalang.sch.id"
                                    required autofocus>
                                @error('email')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" id="password" name="password"
                                        class="w-full px-4 py-3 pr-12 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-800 dark:text-white outline-none transition-all @error('password') border-red-400 @enderror"
                                        placeholder="••••••••"
                                        required>
                                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                @error('password')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- CAPTCHA --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    Kode Keamanan
                                </label>
                                <div class="flex items-center gap-3">
                                    {{-- Kotak tampilan kode --}}
                                    <div id="captcha-display"
                                         class="flex-shrink-0 px-5 py-3 bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl font-mono text-xl font-bold tracking-[0.4em] text-gray-800 dark:text-gray-100 select-none text-center min-w-[9rem]"
                                         style="letter-spacing:0.4em">
                                        {{ $captcha ?? session('login_captcha', '') }}
                                    </div>
                                    {{-- Input kode --}}
                                    <input type="text" name="captcha" id="captcha_input"
                                           inputmode="numeric" maxlength="6" autocomplete="off"
                                           placeholder="Ketik kode"
                                           class="flex-1 px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-800 dark:text-white outline-none transition-all tracking-widest font-mono @error('captcha') border-red-400 @enderror">
                                    {{-- Tombol refresh --}}
                                    <button type="button" id="refresh-captcha"
                                            title="Ganti kode"
                                            class="flex-shrink-0 p-3 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-school-green hover:border-school-green dark:hover:border-green-500 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('captcha')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-school-green focus:ring-green-200">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Ingat saya</span>
                                </label>
                                @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-school-green hover:underline">Lupa password?</a>
                                @endif
                            </div>

                            <button type="submit" :disabled="loading"
                                class="w-full py-3 bg-school-green text-white font-semibold rounded-xl hover:bg-school-green-dark transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="loading ? 'Memproses...' : 'Masuk'">Masuk</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Step 2: OTP Verification --}}
                <div x-show="step === 'otp'" @if(!session('otp_sent')) style="display:none" @endif>
                    <div class="mb-8 text-center">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white font-heading">Verifikasi OTP</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Masukkan kode 6 digit yang dikirim ke email Anda</p>
                    </div>

                    @if(session('dev_otp'))
                    <div class="mb-5 p-4 bg-yellow-50 border border-yellow-300 rounded-xl text-center">
                        <p class="text-xs text-yellow-700 font-medium mb-1">🔧 DEV MODE — Kode OTP:</p>
                        <p class="text-2xl font-bold tracking-[0.5em] text-yellow-800">{{ session('dev_otp') }}</p>
                    </div>
                    @endif

                    @error('otp')
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{ route('otp.verify') }}">
                        @csrf
                        <input type="hidden" name="email" :value="email">
                        <div class="flex justify-center gap-3 mb-6">
                            @for($i = 0; $i < 6; $i++)
                            <input type="text" name="otp[]" maxlength="1" class="otp-input" inputmode="numeric" pattern="[0-9]" required aria-label="Digit OTP {{ $i + 1 }}">
                            @endfor
                        </div>

                        <button type="submit" class="w-full py-3 bg-school-green text-white font-semibold rounded-xl hover:bg-school-green-dark transition-all shadow-md">
                            Verifikasi
                        </button>

                        <div class="mt-4 text-center">
                            <button type="button" @click="step = 'credentials'" class="text-sm text-gray-500 hover:text-school-green">
                                ← Kembali ke login
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('home') }}" class="text-sm text-gray-400 hover:text-school-green transition-colors flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Website
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var refreshUrl = @json(route('captcha.refresh'));
        var btn = document.getElementById('refresh-captcha');
        if (!btn) return;

        btn.addEventListener('click', function () {
            btn.disabled = true;
            btn.classList.add('opacity-50');

            fetch(refreshUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var display = document.getElementById('captcha-display');
                    var input   = document.getElementById('captcha_input');
                    if (display) display.textContent = data.captcha;
                    if (input)   { input.value = ''; input.focus(); }
                })
                .catch(function () { /* silent — user bisa reload halaman */ })
                .finally(function () {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50');
                });
        });
    })();
</script>

</body>
</html>
