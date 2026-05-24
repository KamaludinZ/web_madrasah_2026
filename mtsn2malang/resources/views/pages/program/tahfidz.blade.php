@extends('layouts.app')
@section('title', 'Visi, Misi dan Tujuan - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Tentang Kami</li><li>/</li><li class="text-white font-medium">Visi, Misi dan Tujuan</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Visi, Misi dan Tujuan</h1>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">
        @if($content)
        <div class="prose prose-lg dark:prose-invert max-w-none prose-headings:text-school-green prose-headings:font-heading">
            {!! $content->content !!}
        </div>
        @else
        <div class="space-y-8">
            <div class="p-8 bg-gradient-to-br from-school-green to-school-green-dark text-white rounded-2xl text-center" data-aos="fade-up">
                <h2 class="text-2xl font-bold font-heading mb-4">Visi</h2>
                <p class="text-xl font-medium italic">"Terwujudnya Madrasah yang Unggul dalam Prestasi, Berkarakter Islami, dan Berwawasan Global"</p>
            </div>
            <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-card" data-aos="fade-up" data-aos-delay="100">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Misi</h2>
                <ol class="space-y-3 text-gray-700 dark:text-gray-300">
                    <li class="flex gap-3"><span class="w-6 h-6 bg-school-green text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</span><span>Menyelenggarakan pendidikan yang berkualitas dan berorientasi pada pengembangan potensi peserta didik secara optimal.</span></li>
                    <li class="flex gap-3"><span class="w-6 h-6 bg-school-green text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</span><span>Menanamkan nilai-nilai keislaman dan akhlak mulia dalam setiap aspek kehidupan madrasah.</span></li>
                    <li class="flex gap-3"><span class="w-6 h-6 bg-school-green text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</span><span>Mengembangkan program-program unggulan yang inovatif dan relevan dengan kebutuhan zaman.</span></li>
                    <li class="flex gap-3"><span class="w-6 h-6 bg-school-green text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">4</span><span>Membangun kerjasama yang sinergis antara madrasah, orang tua, dan masyarakat.</span></li>
                    <li class="flex gap-3"><span class="w-6 h-6 bg-school-green text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">5</span><span>Meningkatkan kompetensi tenaga pendidik dan kependidikan secara berkelanjutan.</span></li>
                </ol>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
