<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $kategori = $request->get('kategori');
        $tag = $request->get('tag');
        $from = $request->get('from');
        $to = $request->get('to');

        $results = collect();

        if ($query || $kategori || $tag) {
            $newsQuery = News::published()->with(['category', 'author']);

            if ($query) {
                $newsQuery->where(function ($q) use ($query) {
                    $q->where('title', 'ilike', "%{$query}%")
                      ->orWhere('excerpt', 'ilike', "%{$query}%")
                      ->orWhere('content', 'ilike', "%{$query}%");
                });
            }

            if ($kategori) {
                $newsQuery->byCategory($kategori);
            }

            if ($tag) {
                $newsQuery->withAnyTags([$tag]);
            }

            if ($from) {
                $newsQuery->where('published_at', '>=', $from);
            }

            if ($to) {
                $newsQuery->where('published_at', '<=', $to . ' 23:59:59');
            }

            $results = $newsQuery->latest('published_at')->paginate(12)->withQueryString();
        }

        $categories = Category::where('is_active', true)->orderBy('order')->get();

        return view('pages.search', compact('results', 'query', 'categories', 'kategori', 'tag'));
    }

    public function ajax(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = News::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                  ->orWhere('excerpt', 'ilike', "%{$query}%");
            })
            ->with('category')
            ->latest('published_at')
            ->take(8)
            ->get()
            ->map(fn($news) => [
                'title' => $news->title,
                'url' => route('berita.show', $news->slug),
                'category' => $news->category?->name,
                'date' => $news->published_at?->format('d M Y'),
                'thumbnail' => $news->thumbnail_url,
            ]);

        return view('components.search-results', compact('results'));
    }
}
