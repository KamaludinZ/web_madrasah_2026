@extends('layouts.app')

@section('title', $news->title . ' - MTsN 2 Kota Malang')
@section('description', $news->excerpt)
@section('og_type', 'article')
@section('og_title', $news->title)
@section('og_description', $news->excerpt)
@section('og_image', $news->thumbnail_url)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Main Article --}}
        <article class="flex-1 min-w-0">
            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 flex-wrap">
                    <li><a href="{{ route('home') }}" class="hover:text-school-green">Beranda</a></li>
                    <li><span>/</span></li>
                    <li><a href="{{ route('berita.index') }}" class="hover:text-school-green">Berita</a></li>
                    <li><span>/</span></li>
                    <li class="text-gray-900 dark:text-white font-medium truncate">{{ Str::limit($news->title, 40) }}</li>
                </ol>
            </nav>

            {{-- Category & Meta --}}
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <span class="category-badge category-badge-green">{{ $news->category->name ?? 'Umum' }}</span>
                <span class="text-sm text-gray-400">{{ $news->published_at?->format('d F Y') }}</span>
            </div>

            {{-- Title --}}
            <h1 class="text-2xl md:text-4xl font-bold font-heading text-gray-900 dark:text-white leading-tight mb-6">{{ $news->title }}</h1>

            {{-- Author & Stats --}}
            <div class="flex flex-wrap items-center gap-4 mb-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-school-green flex items-center justify-center text-white text-sm font-bold">
                        {{ substr($news->author->name ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $news->author->name ?? 'Admin' }}</div>
                        <div class="text-xs text-gray-400">Penulis</div>
                    </div>
                </div>
                <div class="flex items-center gap-1 text-sm text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>{{ number_format($news->view_count) }} kali dibaca</span>
                </div>
                <div class="flex items-center gap-1 text-sm text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $news->published_at?->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            {{-- Featured Image --}}
            @if($news->thumbnail)
            <figure class="mb-8 rounded-2xl overflow-hidden">
                <img src="{{ $news->thumbnail_url }}" alt="{{ $news->title }}" class="w-full max-h-96 object-cover">
                @if($news->thumbnail_caption)
                <figcaption class="text-xs text-gray-400 text-center mt-2 italic">{{ $news->thumbnail_caption }}</figcaption>
                @endif
            </figure>
            @endif

            {{-- Content --}}
            <div class="prose prose-lg dark:prose-invert max-w-none prose-headings:font-heading prose-a:text-school-green prose-img:rounded-xl">
                {!! $news->content !!}
            </div>

            {{-- Tags --}}
            @if($news->tags->count() > 0)
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tag:</span>
                    @foreach($news->tags as $tag)
                    <a href="{{ route('search.index', ['tag' => $tag->slug]) }}" class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm rounded-full hover:bg-school-green hover:text-white transition-colors">
                        #{{ $tag->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Share Buttons --}}
            <div class="mt-6 p-5 bg-gray-50 dark:bg-gray-800 rounded-2xl">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Bagikan Berita Ini:</p>
                <div class="flex flex-wrap gap-3">
                    <a href="https://wa.me/?text={{ urlencode($news->title . ' - ' . url()->current()) }}" target="_blank" rel="noopener"
                        class="flex items-center gap-2 px-4 py-2 bg-green-500 text-white text-sm rounded-xl hover:bg-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </a>
                    <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($news->title) }}" target="_blank" rel="noopener"
                        class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white text-sm rounded-xl hover:bg-sky-600 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        Telegram
                    </a>
                    <button onclick="navigator.clipboard.writeText('{{ url()->current() }}').then(() => alert('Link disalin!'))"
                        class="flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Salin Link
                    </button>
                </div>
            </div>

            {{-- Comments --}}
            <div class="mt-8">
                <h3 class="text-xl font-bold font-heading text-gray-900 dark:text-white mb-6">Komentar</h3>

                {{-- Comment Form --}}
                @auth
                <form action="{{ route('komentar.store', $news->id) }}" method="POST" class="mb-8">
                    @csrf
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-full bg-school-green flex items-center justify-center text-white font-bold flex-shrink-0">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <textarea name="content" rows="3" placeholder="Tulis komentar..." class="w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-800 dark:text-white outline-none resize-none transition-all" required></textarea>
                            <button type="submit" class="mt-2 btn-primary text-sm py-2 px-4">Kirim Komentar</button>
                        </div>
                    </div>
                </form>
                @else
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl text-sm text-gray-600 dark:text-gray-400">
                    <a href="{{ route('login') }}" class="text-school-green font-semibold hover:underline">Login</a> untuk meninggalkan komentar.
                </div>
                @endauth

                {{-- Comments List --}}
                <div class="space-y-4">
                    @forelse($comments as $comment)
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold flex-shrink-0">
                            {{ substr($comment->user->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="flex-1 bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-sm text-gray-900 dark:text-white">{{ $comment->user->name ?? 'Anonim' }}</span>
                                <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $comment->content }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-400 text-sm text-center py-6">Belum ada komentar. Jadilah yang pertama!</p>
                    @endforelse
                </div>
            </div>
        </article>

        {{-- Sidebar --}}
        <aside class="lg:w-80 flex-shrink-0">
            {{-- Related News --}}
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Berita Lainnya</h3>
                <div class="space-y-4">
                    @foreach($relatedNews as $related)
                    <a href="{{ route('berita.show', $related->slug) }}" class="flex gap-3 group">
                        <img src="{{ $related->thumbnail_url }}" alt="{{ $related->title }}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-school-green transition-colors line-clamp-2">{{ $related->title }}</h4>
                            <span class="text-xs text-gray-400">{{ $related->published_at?->format('d M Y') }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Category --}}
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Kategori Terkait</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($categories as $cat)
                    <a href="{{ route('berita.index', ['kategori' => $cat->slug]) }}" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm rounded-full hover:bg-school-green hover:text-white transition-colors">{{ $cat->name }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Infografis --}}
            @if($infografis->count() > 0)
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Infografis</h3>
                <div class="space-y-3">
                    @foreach($infografis as $info)
                    <a href="{{ $info->image_url }}" class="glightbox block rounded-xl overflow-hidden group">
                        <img src="{{ $info->image_url }}" alt="{{ $info->title }}" class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>
@endsection
