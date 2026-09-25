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

        if (is_string($kategori = $request->query('kategori')) && $kategori !== '') {
            $query->byCategory($kategori);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('published_at', (int) $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('published_at', (int) $request->bulan);
        }

        $news = $query->latest('published_at')->paginate(12)->withQueryString();

        $categories = Category::where('is_active', true)
            ->withCount(['news' => fn($q) => $q->published()])
            ->orderBy('order')
            ->get();

        $popularNews = News::popular()->with(['category'])->take(5)->get();
        $infografis = Infografis::where('is_active', true)->orderBy('order')->take(4)->get();

        $archiveRows = News::published()
            ->selectRaw('EXTRACT(YEAR FROM published_at)::int as year, EXTRACT(MONTH FROM published_at)::int as month, COUNT(*) as total')
            ->whereNotNull('published_at')
            ->groupByRaw('EXTRACT(YEAR FROM published_at), EXTRACT(MONTH FROM published_at)')
            ->orderByRaw('EXTRACT(YEAR FROM published_at) DESC, EXTRACT(MONTH FROM published_at) DESC')
            ->get();

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $archives = [];
        foreach ($archiveRows as $row) {
            $year = (int) $row->year;
            $month = (int) $row->month;
            $count = (int) $row->total;

            if (!isset($archives[$year])) {
                $archives[$year] = [
                    'year' => $year,
                    'total' => 0,
                    'months' => [],
                ];
            }

            $archives[$year]['total'] += $count;
            $archives[$year]['months'][] = [
                'month' => $month,
                'label' => $monthNames[$month] ?? (string) $month,
                'total' => $count,
            ];
        }

        $selectedYear = $request->filled('tahun') ? (int) $request->tahun : null;
        $selectedMonth = $request->filled('bulan') ? (int) $request->bulan : null;

        return view('pages.berita.index', compact(
            'news',
            'categories',
            'popularNews',
            'infografis',
            'archives',
            'selectedYear',
            'selectedMonth'
        ));
    }

    public function show(string $slug)
    {
        $news = News::published()->with(['category', 'author'])->where('slug', $slug)->firstOrFail();
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
        $author = \App\Models\User::where('name', $username)->firstOrFail();

        $news = News::published()
            ->where('user_id', $author->id)
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(12);

        $categories = Category::where('is_active', true)
            ->withCount(['news' => fn($q) => $q->published()])
            ->orderBy('order')
            ->get();

        $popularNews = News::popular()
            ->where('user_id', $author->id)
            ->with(['category'])
            ->take(5)
            ->get();

        // Jika penulis tidak punya berita populer, ambil berita populer umum
        if ($popularNews->isEmpty()) {
            $popularNews = News::popular()->with(['category'])->take(5)->get();
        }

        return view('pages.berita.author', compact('news', 'author', 'categories', 'popularNews'));
    }

    public function storeComment(Request $request, int $newsId)
    {
        $request->validate(['content' => 'required|string|max:1000']);

        $news = News::findOrFail($newsId);

        if (!$news->allow_comments) {
            return back()->with('error', 'Komentar tidak diizinkan pada berita ini.');
        }

        $user = auth()->user();

        // Editor ke atas langsung approved, user biasa masuk moderasi
        $autoApprove = $user->hasAnyRole(['super_admin', 'editor_in_chief', 'editor', 'moderator']);

        Comment::create([
            'news_id' => $newsId,
            'user_id' => $user->id,
            'content' => $request->content,
            'status'  => $autoApprove ? 'approved' : 'pending',
        ]);

        $message = $autoApprove
            ? 'Komentar berhasil ditambahkan.'
            : 'Komentar berhasil dikirim dan menunggu moderasi.';

        return back()->with('success', $message);
    }
}
