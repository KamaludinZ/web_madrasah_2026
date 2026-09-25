@extends('layouts.app')
@section('title', 'Pengaduan Masyarakat - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Pengaduan</li><li>/</li><li class="text-white font-medium">Pengaduan Masyarakat</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Pengaduan Masyarakat</h1>
        <p class="text-green-100 mt-2">Sampaikan pengaduan, saran, atau masukan Anda kepada kami</p>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-2xl mx-auto">
        @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-8">
            <h2 class="text-xl font-bold font-heading text-gray-900 dark:text-white mb-6">Form Pengaduan</h2>
            <form action="{{ route('pengaduan.masyarakat.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="masyarakat">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama (Opsional)</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-all" placeholder="Nama Anda">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email (Opsional)</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-all" placeholder="email@example.com">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nomor Telepon (Opsional)</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-all" placeholder="08xxxxxxxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Subjek Pengaduan <span class="text-red-500">*</span></label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-all" placeholder="Subjek pengaduan Anda">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Isi Pengaduan <span class="text-red-500">*</span></label>
                        <textarea name="content" required rows="5" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-all resize-none" placeholder="Deskripsikan pengaduan Anda secara detail...">{{ old('content') }}</textarea>
                    </div>
                    <button type="submit" class="w-full btn-primary justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Pengaduan
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-sm text-blue-700 dark:text-blue-400">
            <p class="font-semibold mb-1">ℹ️ Informasi</p>
            <p>Pengaduan Anda akan diproses dalam 3-5 hari kerja. Simpan nomor tiket untuk melacak status pengaduan Anda.</p>
            <p class="mt-1">Untuk pelacakan: <a href="{{ route('pengaduan.pelacakan') }}" class="underline font-semibold">Lacak Pengaduan</a></p>
        </div>
    </div>
</div>
@endsection
