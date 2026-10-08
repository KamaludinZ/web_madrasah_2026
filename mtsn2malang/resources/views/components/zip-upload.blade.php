@props(['model', 'id', 'label', 'limit', 'accent' => 'red'])

{{--
    Input upload file .zip besar (backup / kode aplikasi) dengan progres yang
    jelas: nama & ukuran file, bilah persen, perkiraan sisa waktu, tombol batal,
    dan pesan bila gagal. Upload dijalankan manual lewat $wire.upload() supaya
    file yang bukan .zip atau melebihi batas server ditolak SEBELUM diunggah.
--}}
@php
    $fileButton = $accent === 'amber'
        ? 'file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-900/30 dark:file:text-amber-300 hover:file:bg-amber-100'
        : 'file:bg-red-50 file:text-red-700 dark:file:bg-red-900/30 dark:file:text-red-300 hover:file:bg-red-100';
@endphp

<div
    x-data="{
        model: @js($model),
        limit: @js((int) $limit),
        state: 'idle', // idle | uploading | done | error
        progress: 0,
        name: '',
        size: 0,
        startedAt: 0,
        error: '',
        pick(event) {
            const file = event.target.files[0];
            if (! file) return;

            this.name = file.name;
            this.size = file.size;
            this.error = '';

            if (! file.name.toLowerCase().endsWith('.zip')) {
                return this.fail('File harus berformat .zip.');
            }
            if (this.limit > 0 && file.size > this.limit) {
                return this.fail('Ukuran file ' + this.fmt(file.size) + ' melebihi batas upload server (' + this.fmt(this.limit) + ').');
            }

            this.state = 'uploading';
            this.progress = 0;
            this.startedAt = Date.now();

            this.$wire.upload(
                this.model,
                file,
                () => { this.state = 'done'; this.progress = 100; },
                () => this.fail('Upload gagal. Periksa koneksi internet lalu pilih file lagi. Jika terus gagal, kemungkinan file melebihi batas server.'),
                (e) => { this.progress = e.detail.progress; },
                () => { this.state = 'idle'; this.progress = 0; },
            );
        },
        cancel() {
            this.$wire.cancelUpload(this.model);
            this.$refs.input.value = '';
            this.state = 'idle';
            this.progress = 0;
        },
        fail(message) {
            this.state = 'error';
            this.error = message;
            this.$refs.input.value = '';
            this.$wire.set(this.model, null);
        },
        fmt(bytes) {
            return bytes >= 1048576
                ? (bytes / 1048576).toFixed(1) + ' MB'
                : Math.max(1, Math.round(bytes / 1024)) + ' KB';
        },
        eta() {
            const elapsed = (Date.now() - this.startedAt) / 1000;
            if (this.progress < 2 || elapsed < 3) return 'menghitung sisa waktu...';
            const left = elapsed * (100 - this.progress) / this.progress;
            return 'sisa ± ' + (left >= 60 ? Math.ceil(left / 60) + ' menit' : Math.ceil(left) + ' detik');
        },
    }"
    {{ $attributes }}
>
    <label for="{{ $id }}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</label>
    <input id="{{ $id }}" x-ref="input" type="file" accept=".zip"
           x-on:change="pick($event)"
           x-bind:disabled="state === 'uploading'"
           class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium {{ $fileButton }} border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-60">

    @if($limit > 0)
    <p x-show="state === 'idle'" class="text-xs text-gray-400 mt-1">Batas ukuran upload server: {{ number_format($limit / 1048576, 0) }} MB.</p>
    @endif

    {{-- Sedang mengunggah --}}
    <div x-show="state === 'uploading'" x-cloak class="mt-2 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 px-3 py-2">
        <div class="flex items-center justify-between gap-2 text-xs text-blue-800 dark:text-blue-300">
            <span class="font-medium truncate">Mengunggah <span class="font-mono" x-text="name"></span></span>
            <span class="font-semibold tabular-nums flex-shrink-0" x-text="progress + '%'"></span>
        </div>
        <div class="mt-1.5 h-2 w-full rounded-full bg-blue-100 dark:bg-blue-900/50 overflow-hidden"
             role="progressbar" aria-valuemin="0" aria-valuemax="100" x-bind:aria-valuenow="progress">
            <div class="h-full rounded-full bg-blue-600 transition-all duration-300" x-bind:style="'width: ' + progress + '%'"></div>
        </div>
        <div class="mt-1.5 flex items-center justify-between gap-2 text-xs text-blue-700/80 dark:text-blue-300/80">
            <span class="tabular-nums" x-text="fmt(size * progress / 100) + ' dari ' + fmt(size) + ' · ' + eta()"></span>
            <button type="button" x-on:click="cancel()" class="font-medium underline hover:no-underline flex-shrink-0">Batalkan</button>
        </div>
        <p class="mt-1 text-xs text-blue-700/80 dark:text-blue-300/80">Jangan tutup atau pindah dari halaman ini sampai upload selesai.</p>
    </div>

    {{-- Selesai --}}
    <div x-show="state === 'done'" x-cloak class="mt-2 flex items-center gap-2 rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 text-xs text-emerald-800 dark:text-emerald-300">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>File siap: <span class="font-mono" x-text="name"></span> (<span x-text="fmt(size)"></span>). Klik tombol lanjutkan.</span>
    </div>

    {{-- Gagal --}}
    <div x-show="state === 'error'" x-cloak class="mt-2 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-3 py-2 text-xs text-red-700 dark:text-red-300" role="alert">
        <span x-text="error"></span>
    </div>

    @error($model) <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>
