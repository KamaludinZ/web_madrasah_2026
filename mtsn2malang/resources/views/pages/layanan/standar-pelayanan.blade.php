@extends('layouts.app')
@section('title', 'Standar Pelayanan - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Layanan</li><li>/</li><li class="text-white font-medium">Standar Pelayanan</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Standar Pelayanan</h1>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">
        @if($services->count() > 0)
            <div class="space-y-6">
                @foreach($services as $service)
                    @php
                        $requirements = is_array($service->requirements) ? $service->requirements : [];
                        $procedures = is_array($service->procedures) ? $service->procedures : [];
                    @endphp

                    <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-card overflow-hidden" data-aos="fade-up" x-data="{ open: false }">
                        <button
                            type="button"
                            class="w-full text-left p-6 md:p-8 flex items-start md:items-center justify-between gap-4 hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                        >
                            <div class="min-w-0">
                                <h2 class="text-xl md:text-2xl font-bold font-heading text-school-green">
                                    {{ $service->name }}
                                </h2>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs md:text-sm">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-gray-700 dark:text-gray-200">
                                        Waktu: {{ $service->service_time ?: '-' }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-gray-700 dark:text-gray-200">
                                        Biaya: {{ $service->cost ?: '-' }}
                                    </span>
                                </div>
                            </div>

                            <span class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200 transition-transform duration-300" :class="{ 'rotate-180': open }">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>

                        <div x-show="open" x-collapse class="px-6 md:px-8 pb-6 md:pb-8 border-t border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-6">
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 p-4">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Waktu Pelayanan</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $service->service_time ?: '-' }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 p-4">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Biaya / Tarif</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $service->cost ?: '-' }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 p-4">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Produk Layanan</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $service->product ?: '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <section>
                                    <h3 class="text-base md:text-lg font-semibold text-school-green mb-3">Persyaratan</h3>
                                    @if(count($requirements) > 0)
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 w-16">No.</th>
                                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 w-56">Komponen</th>
                                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Uraian</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($requirements as $item)
                                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item['no'] ?? '-' }}</td>
                                                            <td class="px-3 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $item['komponen'] ?? '-' }}</td>
                                                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item['uraian'] ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Belum ada data persyaratan.</p>
                                    @endif
                                </section>

                                <section>
                                    <h3 class="text-base md:text-lg font-semibold text-school-green mb-3">Prosedur</h3>
                                    @if(count($procedures) > 0)
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 w-16">No.</th>
                                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200 w-56">Komponen</th>
                                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Uraian</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($procedures as $item)
                                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item['no'] ?? '-' }}</td>
                                                            <td class="px-3 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $item['komponen'] ?? '-' }}</td>
                                                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item['uraian'] ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Belum ada data prosedur.</p>
                                    @endif
                                </section>

                                <section>
                                    <h3 class="text-base md:text-lg font-semibold text-school-green mb-2">Pengelolaan Pengaduan</h3>
                                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 p-4 prose prose-sm dark:prose-invert max-w-none prose-headings:text-school-green">
                                        @if($service->complaint_handling)
                                            {!! $service->complaint_handling !!}
                                        @else
                                            <p class="text-gray-500 dark:text-gray-400">-</p>
                                        @endif
                                    </div>
                                </section>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-card" data-aos="fade-up">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Standar Pelayanan</h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    Data standar pelayanan belum tersedia. Silakan tambahkan melalui panel admin.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
