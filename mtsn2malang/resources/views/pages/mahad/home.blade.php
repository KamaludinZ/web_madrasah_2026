@extends('layouts.app')

@section('title', 'Website Ma\'had - MTsN 2 Kota Malang')
@section('description', 'Landing page khusus Website Ma\'had MTsN 2 Kota Malang.')

@section('content')

<section class="py-16 bg-gradient-to-br from-school-green-dark to-school-green text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl">
            <span class="inline-block px-3 py-1 bg-school-orange text-white text-sm font-semibold rounded-full mb-4">Website Ma'had</span>
            <h1 class="text-4xl md:text-5xl font-bold font-heading mb-4">Selamat Datang di Website Ma'had</h1>
            <p class="text-green-100 text-lg">Pusat informasi program, kegiatan, berita, dan layanan Ma'had MTsN 2 Kota Malang.</p>
        </div>
    </div>
</section>

@if($programs->count() > 0)
<section class="py-12 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="section-divider mb-8">
            <h2 class="section-title">Program Ma'had</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($programs as $program)
            <article class="bg-gray-50 dark:bg-gray-800 rounded-2xl overflow-hidden shadow-card">
                <img src="{{ $program->cover_url }}" alt="{{ $program->name }}" class="w-full h-48 object-cover" loading="lazy">
                <div class="p-5">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2">{{ $program->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3">{{ $program->description }}</p>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-12 bg-gray-50 dark:bg-gray-800/50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="section-divider">
                <h2 class="section-title">Berita Ma'had</h2>
            </div>
            <a href="{{ route('mahad.berita') }}" class="text-school-green hover:text-school-green-dark font-semibold text-sm flex items-center gap-1">
                Lihat Semua
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($latestNews as $news)
            <article class="news-card">
                <a href="{{ route('berita.show', $news->slug) }}" class="block overflow-hidden">
                    <img src="{{ $news->thumbnail_url }}" alt="{{ $news->title }}" class="news-card-image" loading="lazy">
                </a>
                <div class="p-5">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                        <a href="{{ route('berita.show', $news->slug) }}">{{ $news->title }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $news->excerpt }}</p>
                </div>
            </article>
            @empty
            <div class="col-span-3 text-center py-10 text-gray-400">Belum ada berita Ma'had.</div>
            @endforelse
        </div>
    </div>
</section>

@if($socialMediaContents->count() > 0)
<section class="py-12 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="section-divider mb-8">
            <h2 class="section-title">Konten Sosial Media</h2>
        </div>

        <div x-data="{ activeSocialTab: 'youtube' }" class="mb-6">
            <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                <button @click="activeSocialTab = 'youtube'" :class="activeSocialTab === 'youtube' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">YouTube</button>
                <button @click="activeSocialTab = 'instagram'" :class="activeSocialTab === 'instagram' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Instagram</button>
                <button @click="activeSocialTab = 'tiktok'" :class="activeSocialTab === 'tiktok' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">TikTok</button>
            </div>

            @foreach(['youtube', 'instagram', 'tiktok'] as $platform)
            <div x-show="activeSocialTab === '{{ $platform }}'" class="mt-6">
                @php $platformItems = $socialMediaContents->where('platform', $platform); @endphp
                <div class="grid gap-6 justify-items-center {{ $platform === 'youtube' ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4' }}">
                    @forelse($platformItems as $content)
                    <article class="bg-gray-50 dark:bg-gray-800 rounded-2xl overflow-hidden shadow-card h-full {{ $content->platform === 'youtube' ? 'w-full max-w-sm' : '' }} {{ $content->platform === 'instagram' ? 'w-56' : '' }} {{ $content->platform === 'tiktok' ? 'w-44' : '' }}">
                        <a href="{{ $content->post_url }}" target="_blank" rel="noopener" class="block p-3 pb-2">
                            <div class="overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700 {{ $content->platform === 'youtube' ? 'aspect-video' : '' }} {{ $content->platform === 'instagram' ? 'aspect-[4/5]' : '' }} {{ $content->platform === 'tiktok' ? 'aspect-[9/16]' : '' }}">
                                <img src="{{ $content->thumbnail_url }}" alt="{{ $content->title }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                        </a>
                        <div class="{{ in_array($content->platform, ['instagram', 'tiktok']) ? 'px-3 pb-4 pt-1' : 'p-5 pt-2' }}">
                            <span class="inline-block mb-2 px-2 py-1 text-xs rounded-full bg-gray-200 text-gray-700">{{ ucfirst($content->platform) }}</span>
                            <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-2 mb-2 leading-snug {{ in_array($content->platform, ['instagram', 'tiktok']) ? 'text-sm min-h-[2.75rem]' : 'text-base min-h-[3rem]' }}">{{ $content->title }}</h3>
                            <a href="{{ $content->post_url }}" target="_blank" rel="noopener" class="text-school-green font-semibold {{ in_array($content->platform, ['instagram', 'tiktok']) ? 'text-xs' : 'text-sm' }}">Lihat Konten</a>
                        </div>
                    </article>
                    @empty
                    <div class="col-span-full text-center py-10 text-gray-400">Belum ada konten {{ ucfirst($platform) }}.</div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($facilities->count() > 0)
<section class="py-12 bg-gray-50 dark:bg-gray-800/50">
    <div class="container mx-auto px-4">
        <div class="section-divider mb-8">
            <h2 class="section-title">Sarana Ma'had</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($facilities as $facility)
            <div class="rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-card">
                <img src="{{ $facility->image_url }}" alt="{{ $facility->name }}" class="w-full h-32 object-cover" loading="lazy">
                <div class="p-3">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $facility->name }}</h4>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
