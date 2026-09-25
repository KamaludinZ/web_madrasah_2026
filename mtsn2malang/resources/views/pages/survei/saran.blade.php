@extends('layouts.app')
@section('title', 'Saran - MTsN 2 Kota Malang')
@section('description', 'Sampaikan saran dan masukan Anda untuk MTsN 2 Kota Malang')
@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Survei</li>
                <li>/</li>
                <li class="text-white font-medium">Saran</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Kotak Saran</h1>
        <p class="text-green-100 mt-2">Sampaikan saran dan masukan Anda untuk kemajuan MTsN 2 Kota Malang</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-2xl mx-auto">

        {{-- Notifikasi sukses --}}
        @if(session('success'))
        <div class="mb-6 p-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-700 dark:text-green-400 text-sm">
                    {{ session('success') }}
                    @if(session('ticket'))
                        Nomor tiket Anda: <strong>{{ session('ticket') }}</strong>. Simpan nomor tiket ini untuk melacak status saran Anda.
                    @endif
                </p>
            </div>
        </div>
        @endif

        {{-- Error validasi --}}
        @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl">
            <ul class="list-disc pl-5 space-y-1 text-sm text-red-700 dark:text-red-400">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Info box --}}
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-blue-700 dark:text-blue-400">
                Saran Anda sangat berarti bagi kami. Anda dapat menyampaikan saran secara anonim. Setiap saran akan mendapatkan nomor tiket sebagai bukti penerimaan.
            </p>
        </div>

        {{-- Form Saran --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Form Saran & Masukan
            </h2>

            <form action="{{ route('survei.saran.store') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Nama & Email --}}
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Nama <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Nama Anda atau kosongkan"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-colors"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Email <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="email@contoh.com"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-colors"
                        >
                    </div>
                </div>

                {{-- Subjek --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Subjek Saran <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="subject"
                        value="{{ old('subject') }}"
                        placeholder="Contoh: Saran untuk fasilitas perpustakaan"
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-colors @error('subject') border-red-400 @enderror"
                    >
                    @error('subject')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Isi Saran --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Isi Saran <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        name="content"
                        rows="6"
                        placeholder="Tuliskan saran atau masukan Anda secara lengkap dan jelas..."
                        required
                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-colors resize-none @error('content') border-red-400 @enderror"
                    >{{ old('content') }}</textarea>
                    @error('content')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Minimal 10 karakter</p>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-between pt-2">
                    <p class="text-xs text-gray-400">
                        <span class="text-red-500">*</span> Wajib diisi
                    </p>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-school-green hover:bg-school-green-dark text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Saran
                    </button>
                </div>
            </form>
        </div>

        {{-- Catatan --}}
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-2xl">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Catatan Penting</h3>
            <ul class="space-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                <li class="flex items-start gap-2">
                    <span class="w-1 h-1 rounded-full bg-gray-400 mt-1.5 flex-shrink-0"></span>
                    Saran yang disampaikan akan ditinjau oleh tim kami dan ditindaklanjuti sesuai kebijakan madrasah.
                </li>
                <li class="flex items-start gap-2">
                    <span class="w-1 h-1 rounded-full bg-gray-400 mt-1.5 flex-shrink-0"></span>
                    Identitas Anda bersifat rahasia dan tidak akan dipublikasikan.
                </li>
                <li class="flex items-start gap-2">
                    <span class="w-1 h-1 rounded-full bg-gray-400 mt-1.5 flex-shrink-0"></span>
                    Simpan nomor tiket yang diberikan sebagai bukti penerimaan saran Anda.
                </li>
            </ul>
        </div>

        {{-- ===== LACAK SARAN ===== --}}
        <div class="mt-10" id="lacak-saran">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                <h2 class="text-base font-bold text-gray-600 dark:text-gray-400 whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Lacak Status Saran
                </h2>
                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Masukkan nomor tiket saran Anda (format: <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs font-mono">SAR-YYYYMMDD-XXXX</code>) untuk melihat status tindak lanjut.
                </p>

                <form action="{{ route('survei.saran.lacak') }}" method="GET" class="flex gap-3">
                    <input
                        type="text"
                        name="tiket"
                        value="{{ request('tiket') }}"
                        placeholder="Contoh: SAR-20260625-4836"
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

                {{-- Hasil tidak ditemukan --}}
                @if(isset($notFound) && $notFound)
                <div class="mt-5 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-700 dark:text-red-400">Nomor tiket tidak ditemukan</p>
                        <p class="text-xs text-red-600 dark:text-red-500 mt-0.5">Pastikan nomor tiket yang Anda masukkan benar. Format: <span class="font-mono">SAR-YYYYMMDD-XXXX</span></p>
                    </div>
                </div>
                @endif

                {{-- Hasil ditemukan --}}
                @if(isset($saran) && $saran)
                @php
                    $statusConfig = match($saran->status) {
                        'pending'  => ['label' => 'Belum Ditinjau',        'bg' => 'bg-yellow-50 dark:bg-yellow-900/20', 'border' => 'border-yellow-200 dark:border-yellow-800', 'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300', 'icon_color' => 'text-yellow-500', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'process'  => ['label' => 'Sedang Ditinjau',       'bg' => 'bg-blue-50 dark:bg-blue-900/20',   'border' => 'border-blue-200 dark:border-blue-800',   'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',   'icon_color' => 'text-blue-500',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        'resolved' => ['label' => 'Sudah Ditindaklanjuti', 'bg' => 'bg-green-50 dark:bg-green-900/20', 'border' => 'border-green-200 dark:border-green-800', 'badge' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300', 'icon_color' => 'text-green-500', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'rejected' => ['label' => 'Tidak Relevan',         'bg' => 'bg-gray-50 dark:bg-gray-700/30',  'border' => 'border-gray-200 dark:border-gray-600',  'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',     'icon_color' => 'text-gray-400',   'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        default    => ['label' => $saran->status,          'bg' => 'bg-gray-50',                       'border' => 'border-gray-200',                       'badge' => 'bg-gray-100 text-gray-700',                                         'icon_color' => 'text-gray-400',   'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    };
                @endphp
                <div class="mt-5 p-5 {{ $statusConfig['bg'] }} border {{ $statusConfig['border'] }} rounded-xl">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 {{ $statusConfig['icon_color'] }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusConfig['icon'] }}"/>
                            </svg>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-100">Detail Saran</span>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusConfig['badge'] }}">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>

                    <div class="space-y-2.5 text-sm">
                        <div class="flex gap-3">
                            <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">No. Tiket</span>
                            <span class="font-mono font-semibold text-gray-800 dark:text-gray-100">{{ $saran->ticket_number }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">Subjek</span>
                            <span class="text-gray-800 dark:text-gray-100">{{ $saran->subject }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">Dikirim</span>
                            <span class="text-gray-800 dark:text-gray-100">{{ $saran->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                        @if($saran->responded_at)
                        <div class="flex gap-3">
                            <span class="text-gray-500 dark:text-gray-400 w-28 flex-shrink-0">Ditinjau</span>
                            <span class="text-gray-800 dark:text-gray-100">{{ $saran->responded_at->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                        @endif
                    </div>

                    {{-- Progress steps --}}
                    <div class="mt-4 pt-4 border-t border-current border-opacity-10">
                        <div class="flex items-center gap-0">
                            @php
                                $steps = [
                                    ['key' => 'pending',  'label' => 'Diterima'],
                                    ['key' => 'process',  'label' => 'Ditinjau'],
                                    ['key' => 'resolved', 'label' => 'Selesai'],
                                ];
                                $order = ['pending' => 0, 'process' => 1, 'resolved' => 2, 'rejected' => -1];
                                $currentOrder = $order[$saran->status] ?? 0;
                            @endphp
                            @if($saran->status === 'rejected')
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Saran ini ditandai tidak relevan oleh tim kami.
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
        {{-- ===== END LACAK SARAN ===== --}}

    </div>
</div>
@endsection
