<div
    x-data="{
        captchaCode: @js($captcha),
        captchaInput: '',
        refreshing: false,
    }"
    x-on:captcha-refreshed.window="captchaCode = $event.detail.captcha; captchaInput = ''; $nextTick(() => $refs.captchaInput?.focus())"
    class="space-y-2"
>
    <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white">
        Kode Keamanan
    </label>

    <div class="flex items-center gap-3">
        {{-- Kotak tampilan kode --}}
        <div
            class="flex-shrink-0 min-w-[9rem] px-4 py-2.5 rounded-lg border-2 border-dashed
                   border-gray-300 dark:border-gray-600
                   bg-gray-50 dark:bg-gray-800
                   font-mono text-xl font-bold tracking-[0.4em]
                   text-gray-800 dark:text-gray-100
                   select-none text-center"
            x-text="captchaCode"
        ></div>

        {{-- Input kode --}}
        <input
            type="text"
            name="captcha"
            wire:model="data.captcha"
            x-ref="captchaInput"
            inputmode="numeric"
            maxlength="6"
            autocomplete="off"
            placeholder="Ketik kode"
            class="flex-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600
                   bg-white dark:bg-gray-900
                   px-3 py-2.5 text-sm font-mono tracking-widest
                   text-gray-900 dark:text-white
                   shadow-sm
                   focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                   dark:focus:border-primary-500
                   transition"
        >

        {{-- Tombol refresh --}}
        <button
            type="button"
            wire:click="refreshCaptcha"
            x-bind:disabled="refreshing"
            x-on:click="refreshing = true; setTimeout(() => refreshing = false, 1000)"
            title="Ganti kode keamanan"
            class="flex-shrink-0 p-2.5 rounded-lg border border-gray-300 dark:border-gray-600
                   text-gray-500 dark:text-gray-400
                   hover:text-primary-600 hover:border-primary-500
                   dark:hover:text-primary-400 dark:hover:border-primary-500
                   disabled:opacity-50 transition"
        >
            <svg
                class="w-5 h-5"
                x-bind:class="{ 'animate-spin': refreshing }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>

    @error('data.captcha')
        <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400">
            {{ $message }}
        </p>
    @enderror
</div>
