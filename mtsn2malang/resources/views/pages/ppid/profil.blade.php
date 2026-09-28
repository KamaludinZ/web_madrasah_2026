@extends('layouts.app')
@section('title', 'Profil PPID - MTsN 2 Kota Malang')
@section('description', 'Profil Pejabat Pengelola Informasi dan Dokumentasi (PPID) MTsN 2 Kota Malang')
@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>PPID</li>
                <li>/</li>
                <li class="text-white font-medium">Profil PPID</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Profil PPID</h1>
        <p class="text-green-100 mt-2">Pejabat Pengelola Informasi dan Dokumentasi MTsN 2 Kota Malang</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">

        {{-- ===== KONTEN FALLBACK (selalu tampil) ===== --}}
        <div class="space-y-8">

            {{-- Pengantar --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tentang PPID</h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Pejabat Pengelola Informasi dan Dokumentasi (PPID) MTsN 2 Kota Malang adalah pejabat yang bertanggung jawab di bidang penyimpanan, pendokumentasian, penyediaan, dan/atau pelayanan informasi di MTsN 2 Kota Malang sesuai dengan Undang-Undang Nomor 14 Tahun 2008 tentang Keterbukaan Informasi Publik.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Dasar Hukum --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Dasar Hukum
                </h2>
                <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-school-green mt-2 flex-shrink-0"></span>
                        <span>UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-school-green mt-2 flex-shrink-0"></span>
                        <span>PP No. 61 Tahun 2010 tentang Pelaksanaan UU KIP</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-school-green mt-2 flex-shrink-0"></span>
                        <span>Peraturan Menteri Agama tentang Pelayanan Informasi Publik di Lingkungan Kementerian Agama</span>
                    </li>
                </ul>
            </div>

            {{-- Tugas & Fungsi --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Tugas & Fungsi PPID
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach([
                        'Menyimpan, mendokumentasikan, menyediakan, dan memberi pelayanan informasi',
                        'Melakukan verifikasi bahan informasi publik',
                        'Melakukan pemutakhiran informasi dan dokumentasi',
                        'Menyediakan informasi dan dokumentasi untuk diakses masyarakat',
                        'Melakukan konsultasi dengan Komisi Informasi apabila diperlukan',
                        'Menyelesaikan sengketa informasi melalui mediasi dan ajudikasi nonlitigasi',
                    ] as $tugas)
                    <div class="flex items-start gap-3 p-3 bg-green-50 dark:bg-green-900/10 rounded-xl">
                        <svg class="w-5 h-5 text-school-green flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $tugas }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Maklumat Pelayanan --}}
            <div class="bg-gradient-to-r from-school-green-dark to-school-green rounded-2xl p-6 text-white">
                <h2 class="text-xl font-bold mb-3">Maklumat Pelayanan Informasi</h2>
                <p class="text-green-100 leading-relaxed">
                    Kami berkomitmen untuk memberikan pelayanan informasi publik yang cepat, tepat, dan akurat sesuai dengan ketentuan peraturan perundang-undangan yang berlaku. Apabila pelayanan yang kami berikan tidak memuaskan, kami siap menerima saran dan pengaduan dari masyarakat.
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('ppid.berkala') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-medium transition-colors">
                        Informasi Berkala
                    </a>
                    <a href="{{ route('ppid.setiap-saat') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-medium transition-colors">
                        Informasi Setiap Saat
                    </a>
                    <a href="{{ route('ppid.serta-merta') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-medium transition-colors">
                        Informasi Serta Merta
                    </a>
                </div>
            </div>

        </div>
        {{-- ===== END KONTEN FALLBACK ===== --}}

        {{-- ===== KONTEN DARI ADMIN (tampil di bawah jika sudah diisi) ===== --}}
        @if($content && $content->content)
        <div class="mt-10">
            <h2 class="text-2xl font-bold font-heading text-school-green mb-6">
                {{ $content->title ?: 'Informasi Tambahan' }}
            </h2>

            @if($content->image)
            <div class="mb-8 rounded-2xl overflow-hidden shadow-card">
                <img src="{{ asset('storage/' . $content->image) }}"
                     alt="{{ $content->title ?? 'Profil PPID' }}"
                     class="w-full h-auto object-contain">
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                <div class="prose prose-lg dark:prose-invert mx-auto
                            prose-headings:text-school-green prose-headings:font-heading
                            prose-a:text-school-green prose-a:no-underline hover:prose-a:underline
                            prose-strong:text-gray-900 dark:prose-strong:text-white">
                    {!! \App\Support\SafeHtml::clean($content->content) !!}
                </div>
            </div>
        </div>
        @endif
        {{-- ===== END KONTEN DARI ADMIN ===== --}}

    </div>
</div>
@endsection
