@props(['name' => null])

{{--
    Ikon Portal (heroicons outline 24px). Nama ikon dipilih admin untuk
    kategori, link, dan Akses Cepat; daftar & path ada di App\Support\PortalIcons
    (nama tak dikenal jatuh ke ikon "link").
--}}
<svg {{ $attributes->merge(['class' => 'w-6 h-6']) }} fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ \App\Support\PortalIcons::path($name) }}"/>
</svg>
