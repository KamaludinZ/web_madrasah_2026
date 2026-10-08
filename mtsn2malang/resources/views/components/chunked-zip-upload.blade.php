@props(['id', 'label', 'endpoint', 'limit', 'onComplete', 'onReset'])

{{--
    Upload file .zip berukuran sangat besar (backup GB) secara bertahap:
    file dipotong di browser lalu dikirim berurutan ke $endpoint, sehingga
    tidak terbentur batas upload server. Potongan yang gagal dicoba ulang
    otomatis. Setelah selesai memanggil method Livewire $onComplete(token, nama).
--}}
<div
    x-data="{
        endpoint: @js($endpoint),
        limit: @js((int) $limit),
        maxChunk: @js(\App\Support\ChunkedUpload::CHUNK_BYTES),
        state: 'idle', // idle | uploading | done | error
        progress: 0,
        sent: 0,
        name: '',
        size: 0,
        startedAt: 0,
        error: '',
        controller: null,
        chunkSize() {
            // Sisakan ruang untuk field lain di request bila batas server kecil.
            const serverCap = this.limit > 0 ? this.limit - 65536 : this.maxChunk;
            return Math.max(262144, Math.min(this.maxChunk, serverCap));
        },
        async pick(event) {
            const file = event.target.files[0];
            if (! file) return;

            this.name = file.name;
            this.size = file.size;
            this.error = '';

            if (! file.name.toLowerCase().endsWith('.zip')) {
                return this.fail('File harus berformat .zip.');
            }

            this.$wire.call(@js($onReset));
            this.state = 'uploading';
            this.progress = 0;
            this.sent = 0;
            this.startedAt = Date.now();
            this.controller = new AbortController();

            const size = this.chunkSize();
            const total = Math.max(1, Math.ceil(file.size / size));
            let token = null;

            try {
                for (let index = 0; index < total; index++) {
                    const blob = file.slice(index * size, Math.min(file.size, (index + 1) * size));
                    const result = await this.send({ token, index, total, file, blob });
                    token = result.token;
                    this.sent = Math.min(file.size, (index + 1) * size);
                    this.progress = Math.floor(this.sent / file.size * 100);
                }

                await this.$wire.call(@js($onComplete), token, file.name);
                this.state = 'done';
                this.progress = 100;
            } catch (e) {
                if (e.name === 'AbortError') return;
                this.fail(e.message || 'Upload gagal. Periksa koneksi internet lalu pilih file lagi.');
            }
        },
        async send({ token, index, total, file, blob }) {
            let lastError = null;

            for (let attempt = 1; attempt <= 4; attempt++) {
                const body = new FormData();
                if (token) body.append('token', token);
                body.append('index', index);
                body.append('total', total);
                body.append('size', file.size);
                body.append('name', file.name);
                body.append('chunk', blob, 'chunk');

                try {
                    const response = await fetch(this.endpoint, {
                        method: 'POST',
                        body,
                        signal: this.controller.signal,
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        },
                    });
                    const json = await response.json().catch(() => ({}));

                    if (response.ok) return json;

                    // Ditolak server dengan alasan jelas — tidak perlu dicoba ulang.
                    if (response.status === 422 || response.status === 403) {
                        throw Object.assign(new Error(json.message || 'Upload ditolak server.'), { final: true });
                    }
                    if (response.status === 419) {
                        throw Object.assign(new Error('Sesi login kedaluwarsa. Muat ulang halaman lalu unggah lagi.'), { final: true });
                    }
                    lastError = new Error('Server merespons ' + response.status + '.');
                } catch (e) {
                    if (e.name === 'AbortError' || e.final) throw e;
                    lastError = e;
                }

                // Koneksi terputus / server sibuk: tunggu sebentar lalu coba lagi.
                await new Promise((resolve) => setTimeout(resolve, attempt * 2000));
            }

            throw new Error('Upload terhenti di ' + this.progress + '% karena koneksi bermasalah (' + (lastError?.message || 'tidak diketahui') + '). Pilih file lagi untuk mengulang.');
        },
        cancel() {
            this.controller?.abort();
            this.$refs.input.value = '';
            this.state = 'idle';
            this.progress = 0;
        },
        fail(message) {
            this.state = 'error';
            this.error = message;
            this.$refs.input.value = '';
        },
        fmt(bytes) {
            if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
            return Math.max(1, Math.round(bytes / 1024)) + ' KB';
        },
        eta() {
            const elapsed = (Date.now() - this.startedAt) / 1000;
            if (this.sent <= 0 || elapsed < 3) return 'menghitung sisa waktu...';
            const speed = this.sent / elapsed;
            const left = (this.size - this.sent) / speed;
            const leftText = left >= 3600
                ? Math.floor(left / 3600) + ' jam ' + Math.ceil((left % 3600) / 60) + ' menit'
                : (left >= 60 ? Math.ceil(left / 60) + ' menit' : Math.ceil(left) + ' detik');
            return this.fmt(speed) + '/detik · sisa ± ' + leftText;
        },
    }"
    {{ $attributes }}
>
    <label for="{{ $id }}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</label>
    <input id="{{ $id }}" x-ref="input" type="file" accept=".zip"
           x-on:change="pick($event)"
           x-bind:disabled="state === 'uploading'"
           class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-red-50 file:text-red-700 dark:file:bg-red-900/30 dark:file:text-red-300 hover:file:bg-red-100 border border-gray-300 dark:border-gray-600 rounded-lg disabled:opacity-60">
    <p x-show="state === 'idle'" class="text-xs text-gray-400 mt-1">File besar (hingga beberapa GB) diunggah bertahap dan otomatis dilanjutkan bila koneksi sempat terputus.</p>

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
            <span class="tabular-nums" x-text="fmt(sent) + ' dari ' + fmt(size) + ' · ' + eta()"></span>
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
</div>
