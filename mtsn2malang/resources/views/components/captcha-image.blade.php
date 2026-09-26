@props(['context'])

{{--
    Gambar kode keamanan berupa angka (App\Support\NumericCaptcha).
    Setiap gambar dimuat = kode baru; gambar dimuat ulang lewat tombol ↻ atau
    event browser "captcha-reload" (dikirim setelah kode dicek).
--}}
<div
    x-data="{
        base: @js(route('captcha.image', $context)),
        src: '',
        reload() { this.src = this.base + '?t=' + Date.now() },
    }"
    x-init="reload()"
    x-on:captcha-reload.window="reload()"
    wire:ignore
    {{ $attributes->merge(['class' => 'flex items-center justify-center gap-3']) }}
>
    <img
        x-bind:src="src"
        alt="Kode keamanan berupa angka"
        width="170"
        height="56"
        class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white select-none"
        draggable="false"
    >
    <button
        type="button"
        x-on:click="reload()"
        title="Ganti gambar kode"
        aria-label="Ganti gambar kode keamanan"
        class="p-2 rounded-lg text-gray-400 hover:text-green-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
    </button>
</div>
