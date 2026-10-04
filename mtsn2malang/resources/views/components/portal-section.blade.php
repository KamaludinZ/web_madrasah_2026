@props(['id', 'title' => null])

{{-- Kartu bagian Halaman Portal (kaca transparan di atas kanvas hijau).
     scroll-mt memberi ruang untuk pemindah tab yang menempel di atas. --}}
<article id="{{ $id }}" {{ $attributes->merge(['class' => 'portal-glass scroll-mt-24 p-5 md:p-8 rounded-2xl text-white']) }}>
    @if($title)
        <h2 class="flex items-center gap-2 text-lg md:text-xl font-bold font-heading mb-4 md:mb-5">
            <span class="w-1.5 h-6 rounded-full bg-school-orange" aria-hidden="true"></span>
            {{ $title }}
        </h2>
    @endif
    {{ $slot }}
</article>
