{{--
    Partial: Berita Terkait
    Props:
      $news  — collection of News
      $title — judul section (default: 'Berita Terkait')
--}}
@if(isset($news) && $news->count())
<section class="mt-10">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-1 h-7 bg-school-green rounded-full"></div>
        <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white">
            {{ $title ?? 'Berita Terkait' }}
        </h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($news as $item)
        <a href="{{ route('berita.show', $item->slug) }}"
           class="group flex flex-col rounded-2xl border border-gray-100 dark:border-gray-700
                  overflow-hidden bg-white dark:bg-gray-800
                  hover:border-school-green hover:shadow-md
                  transition-all duration-200">

            {{-- Gambar utama --}}
            <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                @if($item->thumbnail)
                    <img src="{{ $item->thumbnail_url }}"
                         alt="{{ $item->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Konten --}}
            <div class="flex flex-col flex-1 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-snug
                           group-hover:text-school-green transition-colors line-clamp-2">
                    {{ $item->title }}
                </h3>
                @if($item->excerpt)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 line-clamp-2 flex-1">
                    {{ $item->excerpt }}
                </p>
                @endif
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        {{ optional($item->published_at)->format('d M Y') }}
                    </span>
                    @if($item->category)
                    <span class="ml-auto text-xs font-medium px-2 py-0.5 rounded-full
                                 bg-school-green/10 text-school-green dark:bg-green-900/30 dark:text-green-400">
                        {{ $item->category->name }}
                    </span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif
