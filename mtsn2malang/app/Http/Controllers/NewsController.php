<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Infografis;
use App\Models\News;
use App\Models\PageView;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        PageView::record('/berita');

        $query = News::published()->with(['category', 'author']);

        if ($request->filled('kategori')) {
            $query->byCategory($request->kategori);
        }

        $news = $query->latest('published_at')->paginate(12);
        $categories = Category::where('is_active', true)->withCount(['news' => fn($q) => $q->published()])->orderBy('order')->get();
        $popularNews = News::popular()->with(['category'])->take(5)->get();
        $infografis = Infografis::where('is_active', true)->orderBy('order')->take(4)->get();

        return view('pages.berita.index', compact('news', 'categories', 'popularNews', 'infografis'));
    }

    public function show(string $slug)
    {
        $news = News::published()->with(['category', 'author', 'tags'])->where('slug', $slug)->firstOrFail();
        $news->incrementViewCount();
        PageView::record('/berita/' . $slug);

        $comments = $news->comments()->with('user')->latest()->get();
        $relatedNews = News::published()
            ->where('id', '!=', $news->id)
            ->where('category_id', $news->category_id)
            ->latest('published_at')
            ->take(5)
            ->get();
        $categories = Category::where('is_active', true)->orderBy('order')->get();
        $infografis = Infografis::where('is_active', true)->orderBy('order')->take(4)->get();

        return view('pages.berita.show', compact('news', 'comments', 'relatedNews', 'categories', 'infografis'));
    }

    public function byAuthor(string $username)
    {
        $news = News::published()
            ->whereHas('author', fn($q) => $q->where('name', $username))
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(12);

        $author = \App\Models\User::where('name', $username)->firstOrFail();

        return view('pages.berita.author', compact('news', 'author'));
    }

    public function storeComment(Request $request, int $newsId)
    {
        $request->validate(['content' => 'required|string|max:1000']);

        $news = News::findOrFail($newsId);

        if (!$news->allow_comments) {
            return back()->with('error', 'Komentar tidak diizinkan pada berita ini.');
        }

        Comment::create([
            'news_id' => $newsId,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'status' => 'approved',
        ]);

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}
