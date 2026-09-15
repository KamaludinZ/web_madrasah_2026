@extends('layouts.app')

@section('title', 'Info PPDB - MTsN 2 Kota Malang')

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Layanan</li>
                <li>/</li>
                <li class="text-white font-medium">Info PPDB</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Info PPDB</h1>
        <p class="text-green-100 mt-2">Informasi penerimaan peserta didik baru MTsN 2 Kota Malang.</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-5xl mx-auto space-y-8">
        @if($registration)
            @if(!empty($registration->schedule) && is_array($registration->schedule))
                <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                    <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Jadwal Pendaftaran</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 pr-4 font-semibold text-gray-700 dark:text-gray-200">Kegiatan</th>
                                    <th class="text-left py-3 pr-4 font-semibold text-gray-700 dark:text-gray-200">Tanggal / Periode</th>
                                    <th class="text-left py-3 font-semibold text-gray-700 dark:text-gray-200">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registration->schedule as $item)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/60">
                                        <td class="py-3 pr-4 text-gray-700 dark:text-gray-200">{{ $item['kegiatan'] ?? '-' }}</td>
                                        <td class="py-3 pr-4 text-gray-700 dark:text-gray-200">{{ $item['tanggal'] ?? '-' }}</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-300">{{ $item['keterangan'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if($registration->general_requirements)
                <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                    <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Persyaratan Umum</h2>
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        {!! $registration->general_requirements !!}
                    </div>
                </section>
            @endif

            @if($registration->specific_requirements)
                <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                    <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Persyaratan Khusus</h2>
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        {!! $registration->specific_requirements !!}
                    </div>
                </section>
            @endif

            <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Dokumen & Pendaftaran</h2>
                <div class="flex flex-wrap gap-3">
                    @if($registration->brochure_file)
                        <a href="{{ asset('storage/' . $registration->brochure_file) }}" target="_blank" rel="noopener" class="btn-primary">
                            Unduh Brosur
                        </a>
                    @endif

                    @if($registration->brochure_link)
                        <a href="{{ $registration->brochure_link }}" target="_blank" rel="noopener" class="btn-secondary">
                            Lihat Brosur Online
                        </a>
                    @endif

                    @if($registration->registration_link)
                        <a href="{{ $registration->registration_link }}" target="_blank" rel="noopener" class="btn-primary">
                            PPDB Online
                        </a>
                    @endif
                </div>
            </section>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-8 text-center">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-2">Informasi PPDB Belum Tersedia</h2>
                <p class="text-gray-600 dark:text-gray-300">Silakan cek kembali nanti atau hubungi admin madrasah untuk informasi terbaru.</p>
            </div>
        @endif
    </div>
</div>
@endsection
