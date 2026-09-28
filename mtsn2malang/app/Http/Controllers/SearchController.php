<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Parameter berupa array (?q[]=x) atau tanggal tidak valid sebelumnya
        // menyebabkan error 500 — abaikan saja.
        $query = $this->stringParam($request, 'q');
        $kategori = $this->stringParam($request, 'kategori') ?: null;
        $tag = $this->stringParam($request, 'tag') ?: null;
        $from = $this->dateParam($request, 'from');
        $to = $this->dateParam($request, 'to');

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
        $query = $this->stringParam($request, 'q');

        if (mb_strlen($query) < 2) {
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

    private function stringParam(Request $request, string $name): string
    {
        $value = $request->query($name);

        return is_string($value) ? mb_substr(trim($value), 0, 100) : '';
    }

    private function dateParam(Request $request, string $name): ?string
    {
        $value = $request->query($name);

        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4)) ? $value : null;
    }
}
