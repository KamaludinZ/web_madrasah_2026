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

        // Sanitasi: escape karakter wildcard LIKE agar tidak bisa dieksploitasi
        $safeQuery = $query ? str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query) : '';

        if ($query || $kategori || $tag) {
            $newsQuery = News::published()->with(['category', 'author']);

            if ($safeQuery) {
                $newsQuery->where(function ($q) use ($safeQuery) {
                    $q->where('title', 'ilike', "%{$safeQuery}%")
                      ->orWhere('excerpt', 'ilike', "%{$safeQuery}%")
                      ->orWhere('content', 'ilike', "%{$safeQuery}%");
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

        // Sanitasi wildcard LIKE
        $safeQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

        $results = News::published()
            ->where(function ($q) use ($safeQuery) {
                $q->where('title', 'ilike', "%{$safeQuery}%")
                  ->orWhere('excerpt', 'ilike', "%{$safeQuery}%");
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
