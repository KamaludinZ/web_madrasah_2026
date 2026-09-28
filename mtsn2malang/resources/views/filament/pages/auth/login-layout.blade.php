{{--
    Layout halaman login panel admin. Tetap memakai layout dasar Filament
    (Livewire, Alpine, notifikasi, mode gelap), tetapi tanpa kotak "simple
    page" bawaan agar tampilan split-panel dari halaman /login lama bisa dipakai.
--}}
<x-filament-panels::layout.base :livewire="$livewire">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])

    {{ $slot }}
</x-filament-panels::layout.base>
