@extends('layouts.app')
@section('title', 'Pelacakan Pengaduan - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold font-heading">Pelacakan Nomor Pengaduan</h1>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-xl mx-auto">
        <form action="{{ route('pengaduan.pelacakan') }}" method="GET" class="mb-8">
            <div class="flex gap-3">
                <input type="text" name="ticket" value="{{ request('ticket') }}" placeholder="Masukkan nomor tiket (contoh: ADU-20240101-0001)" class="flex-1 px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-800 dark:text-white outline-none">
                <button type="submit" class="btn-primary">Lacak</button>
            </div>
        </form>

        @if(request('ticket') && !$complaint)
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
            Nomor tiket tidak ditemukan. Pastikan nomor tiket yang Anda masukkan benar.
        </div>
        @endif

        @if($complaint)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 dark:text-white">{{ $complaint->ticket_number }}</h2>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ match($complaint->status) { 'pending' => 'bg-gray-100 text-gray-600', 'process' => 'bg-yellow-100 text-yellow-700', 'resolved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-600' } }}">
                    {{ match($complaint->status) { 'pending' => 'Menunggu', 'process' => 'Diproses', 'resolved' => 'Selesai', 'rejected' => 'Ditolak', default => $complaint->status } }}
                </span>
            </div>
            <div class="space-y-3 text-sm">
                <div><span class="text-gray-500">Subjek:</span> <span class="font-medium text-gray-900 dark:text-white">{{ $complaint->subject }}</span></div>
                <div><span class="text-gray-500">Tanggal:</span> <span class="text-gray-700 dark:text-gray-300">{{ $complaint->created_at->format('d M Y H:i') }}</span></div>
                @if($complaint->response)
                <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                    <p class="font-semibold text-green-800 dark:text-green-400 mb-2">Tanggapan:</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $complaint->response }}</p>
                    @if($complaint->responded_at)<p class="text-xs text-gray-400 mt-2">{{ $complaint->responded_at->format('d M Y H:i') }}</p>@endif
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
