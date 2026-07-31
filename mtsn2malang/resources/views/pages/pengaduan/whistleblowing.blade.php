@extends('layouts.app')
@section('title', 'Whistleblowing - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold font-heading">Whistleblowing System</h1>
        <p class="text-green-200 mt-2">Sampaikan laporan dugaan pelanggaran secara aman dan bertanggung jawab.</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto space-y-6">
        @if(session('success'))
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($content) && $content && !empty($content->content))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 prose dark:prose-invert max-w-none">
                {!! $content->content !!}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Form Laporan Whistleblowing</h2>
            <form action="{{ route('pengaduan.whistleblowing.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="type" value="whistleblowing">

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama (Opsional)</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email (Opsional)</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Telepon (Opsional)</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Subjek Laporan</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Isi Laporan</label>
                    <textarea name="content" rows="6" required class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none">{{ old('content') }}</textarea>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="btn-primary">Kirim Laporan</button>
                    <a href="{{ route('pengaduan.pelacakan') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Lacak Aduan Saya
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-sm text-blue-800 dark:text-blue-300">
            Setelah laporan dikirim, Anda akan mendapatkan <strong>nomor tiket</strong>. Simpan nomor tiket tersebut untuk pelacakan pada menu <a href="{{ route('pengaduan.pelacakan') }}" class="underline font-medium">/pengaduan/pelacakan</a>.
        </div>
    </div>
</div>
@endsection
