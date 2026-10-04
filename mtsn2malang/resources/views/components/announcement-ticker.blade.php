{{--
    Teks berjalan pengumuman. Satu sumber data untuk header situs dan
    Halaman Portal: Setting announcement_text & announcement_speed (detik,
    5–120; di luar rentang dipakai 30). Teks kosong = ticker tidak tampil.
--}}
@php
    $announcement = \App\Models\Setting::get('announcement_text', 'Selamat datang di website resmi MTsN 2 Kota Malang. Madrasah Tsanawiyah Negeri 2 Kota Malang - Unggul, Berkarakter, dan Berwawasan Global.');
    $announcementSpeed = (int) \App\Models\Setting::get('announcement_speed', 30);
    if ($announcementSpeed < 5 || $announcementSpeed > 120) {
        $announcementSpeed = 30;
    }
@endphp
@if($announcement)
<div {{ $attributes->merge(['class' => 'news-ticker py-1.5 px-4']) }} style="--ticker-duration: {{ $announcementSpeed }}s;">
    <span class="ticker-label">Pengumuman</span>
    <div class="ticker-content px-4">
        <span class="ticker-text">{{ $announcement }}</span>
    </div>
</div>
@endif
