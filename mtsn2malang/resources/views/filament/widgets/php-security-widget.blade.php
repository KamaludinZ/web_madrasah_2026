{{-- Panel admin tanpa tema Tailwind khusus: pakai komponen bawaan Filament +
     inline style agar tampilan tidak bergantung pada kelas yang ter-compile. --}}
<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-shield-exclamation"
        icon-color="danger"
        :heading="'Peringatan keamanan server: ' . $advisory"
        style="border-left: 4px solid rgb(var(--danger-500));"
    >
        <div style="display: grid; gap: .5rem; font-size: .875rem; line-height: 1.5;">
            @if($vulnerable)
                <p>
                    Server menjalankan <strong>PHP {{ $version }}</strong> yang rentan diretas tanpa login
                    (remote code execution). {{ $recommendation }}
                </p>
                <ul style="list-style: disc; padding-left: 1.25rem; display: grid; gap: .25rem;">
                    <li><strong>Docker/Coolify:</strong> klik <em>Redeploy</em> agar image dibangun ulang dengan PHP terbaru.</li>
                    <li><strong>cPanel:</strong> buka <em>MultiPHP Manager</em> / <em>Select PHP Version</em> lalu pilih versi terbaru, atau minta penyedia hosting memperbarui PHP.</li>
                </ul>
            @endif

            @if($uploadProgress)
                <p>
                    Pengaturan <code>session.upload_progress.enabled</code> masih <strong>On</strong>. Matikan (Off) lewat
                    <em>MultiPHP INI Editor</em> di cPanel atau php.ini server — aplikasi ini tidak memakainya.
                </p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
