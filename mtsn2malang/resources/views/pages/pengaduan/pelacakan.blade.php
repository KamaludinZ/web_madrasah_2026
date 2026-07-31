@extends('layouts.app')
@section('title', 'Pelacakan Pengaduan - MTsN 2 Kota Malang')
@section('description', 'Lacak status pengaduan Anda di MTsN 2 Kota Malang')
@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4">
            <ol class="flex items-center gap-2 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Pengaduan</li>
                <li>/</li>
                <li class="text-white font-medium">Pelacakan</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Lacak Status Pengaduan</h1>
        <p class="text-green-200 mt-2">Masukkan nomor tiket untuk melihat status tindak lanjut pengaduan Anda</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-2xl mx-auto">

        {{-- Form Lacak --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Masukkan nomor tiket pengaduan Anda (format: <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs font-mono">ADU-YYYYMMDD-XXXX</code>) untuk melihat status tindak lanjut.
            </p>

            <form action="{{ route('pengaduan.pelacakan') }}" method="GET" class="flex gap-3">
                <input
                    type="text"
                    name="ticket"
                    value="{{ request('ticket') }}"
                    placeholder="Contoh: ADU-20260625-1234"
                    class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-mono focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-colors"
                    required
                >
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-school-green hover:bg-school-green-dark text-white text-sm font-semibold rounded-xl transition-colors whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Lacak
                </button>
            </form>

            {{-- Tidak ditemukan --}}
            @if(request('ticket') && !$complaint)
            <div class="mt-5 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-700 dark:text-red-400">Nomor tiket tidak ditemukan</p>
                    <p class="text-xs text-red-600 dark:text-red-500 mt-0.5">Pastikan nomor tiket yang Anda masukkan benar. Format: <span class="font-mono">ADU-YYYYMMDD-XXXX</span></p>
                </div>
            </div>
            @endif

            {{-- Hasil ditemukan --}}
            @if($complaint)
            @php
                $statusConfig = match($complaint->status) {
                    'pending'  => ['label' => 'Menunggu',  'bg' => 'bg-yellow-50 dark:bg-yellow-900/20', 'border' => 'border-yellow-200 dark:border-yellow-800', 'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300', 'icon_color' => 'text-yellow-500', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'process'  => ['label' => 'Diproses',  'bg' => 'bg-blue-50 dark:bg-blue-900/20',   'border' => 'border-blue-200 dark:border-blue-800',   'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',   'icon_color' => 'text-blue-500',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    'resolved' => ['label' => 'Selesai',   'bg' => 'bg-green-50 dark:bg-green-900/20', 'border' => 'border-green-200 dark:border-green-800', 'badge' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300', 'icon_color' => 'text-green-500', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'rejected' => ['label' => 'Ditolak',   'bg' => 'bg-gray-50 dark:bg-gray-700/30',  'border' => 'border-gray-200 dark:border-gray-600',  'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',     'icon_color' => 'text-gray-400',   'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    default    => ['label' => $complaint->status, 'bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'badge' => 'bg-gray-100 text-gray-700', 'icon_color' => 'text-gray-400', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                };
            @endphp
            <div class="mt-5 p-5 {{ $statusConfig['bg'] }} border {{ $statusConfig['border'] }} rounded-xl">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 {{ $statusConfig['icon_color'] }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusConfig['icon'] }}"/>
                        </svg>
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">Detail Pengaduan</span>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusConfig['badge'] }}">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>

                <div class="space-y-2.5 text-sm">
                    <div class="flex gap-3">
                        <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">No. Tiket</span>
                        <span class="font-mono font-semibold text-gray-800 dark:text-gray-100">{{ $complaint->ticket_number }}</span>
                    </div>
                    <div class="flex gap-3">
                        <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">Jenis</span>
                        <span class="text-gray-800 dark:text-gray-100">
                            {{ match($complaint->type) { 'whistleblowing' => 'Whistleblowing', 'masyarakat' => 'Pengaduan Masyarakat', default => $complaint->type } }}
                        </span>
                    </div>
                    <div class="flex gap-3">
                        <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">Subjek</span>
                        <span class="text-gray-800 dark:text-gray-100">{{ $complaint->subject }}</span>
                    </div>
                    <div class="flex gap-3">
                        <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">Dikirim</span>
                        <span class="text-gray-800 dark:text-gray-100">{{ $complaint->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                    @if($complaint->responded_at)
                    <div class="flex gap-3">
                        <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">Ditanggapi</span>
                        <span class="text-gray-800 dark:text-gray-100">{{ $complaint->responded_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                    @endif
                </div>

                {{-- Tanggapan admin --}}
                @if($complaint->response)
                <div class="mt-4 p-4 bg-white/60 dark:bg-gray-800/60 border border-current border-opacity-10 rounded-xl">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Tanggapan</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $complaint->response }}</p>
                </div>
                @endif

                {{-- Progress steps --}}
                <div class="mt-4 pt-4 border-t border-current border-opacity-10">
                    <div class="flex items-center gap-0">
                        @php
                            $steps = [
                                ['key' => 'pending',  'label' => 'Diterima'],
                                ['key' => 'process',  'label' => 'Diproses'],
                                ['key' => 'resolved', 'label' => 'Selesai'],
                            ];
                            $order = ['pending' => 0, 'process' => 1, 'resolved' => 2, 'rejected' => -1];
                            $currentOrder = $order[$complaint->status] ?? 0;
                        @endphp
                        @if($complaint->status === 'rejected')
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pengaduan ini telah ditolak oleh tim kami.
                            </div>
                        @else
                            @foreach($steps as $i => $step)
                            @php $done = $currentOrder >= $i; @endphp
                            <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                                <div class="flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $done ? 'bg-school-green text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-400' }}">
                                        @if($done)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </div>
                                    <span class="text-xs mt-1 {{ $done ? 'text-school-green font-semibold' : 'text-gray-400' }} whitespace-nowrap">{{ $step['label'] }}</span>
                                </div>
                                @if($i < count($steps) - 1)
                                <div class="flex-1 h-0.5 mx-1 mb-4 {{ $currentOrder > $i ? 'bg-school-green' : 'bg-gray-200 dark:bg-gray-600' }}"></div>
                                @endif
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
