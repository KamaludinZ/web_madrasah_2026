@extends('layouts.app')
@section('title', 'Kontak Kami - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Tentang Kami</li><li>/</li><li class="text-white font-medium">Kontak</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Kontak Kami</h1>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Contact Info --}}
        <div>
            <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white mb-6">Informasi Kontak</h2>
            <div class="space-y-5">
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-school-green" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Alamat</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Jl. Raya Cemorokandang No. 77<br>Kota Malang, Jawa Timur 65138</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-school-orange" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Telepon</h3>
                        <a href="tel:0341711500" class="text-school-green hover:underline text-sm">(0341) 711500</a>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">WhatsApp PTSP</h3>
                        <a href="https://wa.me/6285163350000" target="_blank" rel="noopener" class="text-school-green hover:underline text-sm">0851 6335 0000</a>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Email</h3>
                        <a href="mailto:mtsnmalang2adm@gmail.com" class="text-school-green hover:underline text-sm">mtsnmalang2adm@gmail.com</a>
                    </div>
                </div>

                {{-- Social Media --}}
                <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Media Sosial</h3>
                    <div class="flex gap-3">
                        <a href="https://www.youtube.com/channel/UCsqzV9zkTBlYZfmOA-_Y63A" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 rounded-lg text-sm hover:bg-red-100 transition-colors">YouTube</a>
                        <a href="https://www.instagram.com/mtsn2kota_malang" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-2 bg-pink-50 dark:bg-pink-900/20 text-pink-600 rounded-lg text-sm hover:bg-pink-100 transition-colors">Instagram</a>
                        <a href="https://web.facebook.com/mtsn2kotamalang" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-lg text-sm hover:bg-blue-100 transition-colors">Facebook</a>
                        <a href="https://www.tiktok.com/@mtsn2kotamalang" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-100 transition-colors">TikTok</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Google Maps --}}
        <div>
            <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white mb-6">Lokasi Kami</h2>
            <div class="rounded-2xl overflow-hidden shadow-card h-96">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3951.4!2d112.6!3d-7.98!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwNTgnNDguMCJTIDExMsKwMzYnMDAuMCJF!5e0!3m2!1sid!2sid!4v1234567890"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi MTsN 2 Kota Malang">
                </iframe>
            </div>
            <div class="mt-4">
                <a href="https://maps.app.goo.gl/RHV4nKSg1eKe3qgo8" target="_blank" rel="noopener" class="btn-primary w-full justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    Buka di Google Maps
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
