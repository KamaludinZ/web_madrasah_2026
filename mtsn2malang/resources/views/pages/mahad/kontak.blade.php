@extends('layouts.app')
@section('title', "Kontak Ma'had - MTsN 2 Kota Malang")
@section('content')

@php
    $mahadName       = \App\Models\Setting::get('mahad_name', "Ma'had Al-Ittihad MTsN 2 Kota Malang");
    $address         = \App\Models\Setting::get('mahad_address', '');
    $phone           = \App\Models\Setting::get('mahad_phone', '');
    $phoneLink       = \App\Models\Setting::get('mahad_phone_link', '');
    $email           = \App\Models\Setting::get('mahad_email', '');
    $wa              = \App\Models\Setting::get('mahad_wa', '');
    $waPengaduan     = \App\Models\Setting::get('mahad_wa_pengaduan', '');
    $waKomite        = \App\Models\Setting::get('mahad_wa_komite', '');
    $mapsUrl         = \App\Models\Setting::get('mahad_maps_url', '');
    $mapsEmbed       = \App\Models\Setting::get('mahad_maps_embed', '');
    $hours           = \App\Models\Setting::get('mahad_hours', '');
    $socialYoutube   = \App\Models\Setting::get('mahad_social_youtube', '');
    $socialInstagram = \App\Models\Setting::get('mahad_social_instagram', '');
    $socialFacebook  = \App\Models\Setting::get('mahad_social_facebook', '');
    $socialTiktok    = \App\Models\Setting::get('mahad_social_tiktok', '');
    $waChannel       = \App\Models\Setting::get('mahad_wa_channel', '');
    $hasSocial       = $socialYoutube || $socialInstagram || $socialFacebook || $socialTiktok || $waChannel;
@endphp

<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li><a href="{{ route('mahad.home') }}" class="hover:text-white">Ma'had</a></li>
                <li>/</li>
                <li class="text-white font-medium">Kontak</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Kontak {{ $mahadName }}</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        {{-- ── Kolom Kiri: Informasi Kontak ── --}}
        <div>
            <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white mb-6">Informasi Kontak</h2>

            @if(!$address && !$phone && !$email && !$wa && !$waPengaduan && !$waKomite && !$hours)
            <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-card text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Informasi kontak Ma'had belum dikonfigurasi.<br>Silakan isi melalui <strong>Pengaturan Website → Kontak Ma'had</strong>.</p>
            </div>
            @else
            <div class="space-y-4">

                {{-- Alamat --}}
                @if($address)
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-school-green" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Alamat</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">{!! nl2br(e($address)) !!}</p>
                    </div>
                </div>
                @endif

                {{-- Telepon --}}
                @if($phone)
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-school-orange" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Telepon</h3>
                        <a href="tel:{{ $phoneLink ?: $phone }}" class="text-school-green hover:underline text-sm">{{ $phone }}</a>
                    </div>
                </div>
                @endif

                {{-- Email --}}
                @if($email)
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Email</h3>
                        <a href="mailto:{{ $email }}" class="text-school-green hover:underline text-sm">{{ $email }}</a>
                    </div>
                </div>
                @endif

                {{-- WhatsApp Ma'had --}}
                @if($wa)
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">WhatsApp Ma'had</h3>
                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="text-school-green hover:underline text-sm">+{{ $wa }}</a>
                    </div>
                </div>
                @endif

                {{-- WhatsApp Pengaduan (opsional) --}}
                @if($waPengaduan)
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">WhatsApp Pengaduan</h3>
                        <a href="https://wa.me/{{ $waPengaduan }}" target="_blank" rel="noopener" class="text-school-green hover:underline text-sm">+{{ $waPengaduan }}</a>
                    </div>
                </div>
                @endif

                {{-- WhatsApp Komite (opsional) --}}
                @if($waKomite)
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">WhatsApp Komite</h3>
                        <a href="https://wa.me/{{ $waKomite }}" target="_blank" rel="noopener" class="text-school-green hover:underline text-sm">+{{ $waKomite }}</a>
                    </div>
                </div>
                @endif

                {{-- Jam Operasional --}}
                @if($hours)
                <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Jam Operasional</h3>
                        <div class="text-gray-600 dark:text-gray-400 text-sm space-y-0.5">
                            @foreach(explode("\n", $hours) as $line)
                                @if(trim($line))
                                    <p>{{ trim($line) }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Sosial Media Ma'had --}}
                @if($hasSocial)
                <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-card">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Ikuti Kami</h3>
                    <div class="flex flex-wrap gap-3">

                        {{-- YouTube --}}
                        @if($socialYoutube)
                        <a href="{{ $socialYoutube }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors text-sm font-medium">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                            YouTube
                        </a>
                        @endif

                        {{-- Instagram --}}
                        @if($socialInstagram)
                        <a href="{{ $socialInstagram }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400 rounded-xl hover:bg-pink-100 dark:hover:bg-pink-900/40 transition-colors text-sm font-medium">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                            Instagram
                        </a>
                        @endif

                        {{-- Facebook --}}
                        @if($socialFacebook)
                        <a href="{{ $socialFacebook }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors text-sm font-medium">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>
                        @endif

                        {{-- TikTok --}}
                        @if($socialTiktok)
                        <a href="{{ $socialTiktok }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                            TikTok
                        </a>
                        @endif

                        {{-- WhatsApp Channel --}}
                        @if($waChannel)
                        <a href="{{ $waChannel }}" target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors text-sm font-medium">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            WhatsApp Channel
                        </a>
                        @endif

                    </div>
                </div>
                @endif

            </div>
            @endif
        </div>

        {{-- ── Kolom Kanan: Google Maps ── --}}
        <div>
            <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white mb-6">Lokasi Ma'had</h2>
            <div class="rounded-2xl overflow-hidden shadow-card h-96">
                @if($mapsEmbed)
                    <iframe
                        src="{{ $mapsEmbed }}"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Lokasi {{ $mahadName }}">
                    </iframe>
                @else
                    <div class="w-full h-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <div class="text-center text-gray-500 dark:text-gray-400 p-6">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm">Peta belum dikonfigurasi.<br>Isi URL Embed Maps di Pengaturan Website → Kontak Ma'had.</p>
                        </div>
                    </div>
                @endif
            </div>
            @if($mapsUrl)
            <div class="mt-4">
                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="btn-primary w-full justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    Buka di Google Maps
                </a>
            </div>
            @endif

            {{-- Link kembali ke halaman utama Ma'had --}}
            <div class="mt-4">
                <a href="{{ route('mahad.home') }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 border-2 border-school-green text-school-green rounded-xl font-semibold hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Halaman Ma'had
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
