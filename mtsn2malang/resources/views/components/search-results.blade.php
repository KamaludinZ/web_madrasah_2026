@if($results->count() > 0)
<div class="space-y-2">
    @foreach($results as $result)
    <a href="{{ $result['url'] }}" class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-xl transition-colors group">
        <img src="{{ $result['thumbnail'] }}" alt="{{ $result['title'] }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-school-green transition-colors truncate">{{ $result['title'] }}</p>
            <div class="flex items-center gap-2 mt-0.5">
                @if($result['category'])<span class="text-xs text-school-green">{{ $result['category'] }}</span>@endif
                <span class="text-xs text-gray-400">{{ $result['date'] }}</span>
            </div>
        </div>
        <svg class="w-4 h-4 text-gray-300 group-hover:text-school-green transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    @endforeach
    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
        <a href="{{ route('search.index', ['q' => request('q')]) }}" class="block text-center text-sm text-school-green hover:underline py-2">Lihat semua hasil pencarian →</a>
    </div>
</div>
@else
<div class="text-center py-8 text-gray-400">
    <p class="text-sm">Tidak ada hasil untuk "<strong>{{ request('q') }}</strong>"</p>
</div>
@endif
