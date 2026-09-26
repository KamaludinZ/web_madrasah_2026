<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Infografis;
use App\Models\News;
use App\Models\PageView;
use App\Support\NumericCaptcha;
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

    public function storeComment(Request $request, int $id)
    {
        $news = News::published()->findOrFail($id);
        $user = auth()->user();
        $redirect = redirect()->to(route('berita.show', $news->slug) . '#komentar');

        if (! $news->allow_comments) {
            return $redirect->with('comment_error', 'Komentar tidak diizinkan pada berita ini.');
        }

        // Honeypot: kolom tersembunyi yang hanya diisi bot. Pura-pura berhasil
        // agar bot tidak tahu komentarnya dibuang.
        if (filled($request->input('website'))) {
            return $redirect->with('comment_success', 'Terima kasih! Komentar Anda akan tampil setelah disetujui moderator.');
        }

        $rules = ['content' => 'required|string|min:3|max:1000'];

        if (! $user) {
            $rules += [
                'guest_name' => 'required|string|max:100',
                'guest_email' => 'nullable|email|max:255',
                'captcha' => 'required|string',
            ];
        }

        $validated = $request->validate($rules, [
            'content.required' => 'Isi komentar wajib diisi.',
            'content.min' => 'Komentar terlalu pendek.',
            'content.max' => 'Komentar maksimal 1000 karakter.',
            'guest_name.required' => 'Nama wajib diisi.',
            'guest_email.email' => 'Format email tidak valid.',
            'captcha.required' => 'Kode keamanan wajib diisi.',
        ]);

        if (! $user && ! NumericCaptcha::check('komentar', $request->input('captcha'))) {
            return $redirect->withInput()->withErrors(['captcha' => 'Kode keamanan tidak sesuai. Silakan coba lagi.']);
        }

        // Staf (editor ke atas / moderator) langsung tampil; komentar tamu dan
        // pengguna lain masuk antrean moderasi.
        $autoApprove = $user?->hasAnyRole(['super_admin', 'editor_in_chief', 'editor', 'moderator']) ?? false;

        Comment::create([
            'news_id' => $news->id,
            'user_id' => $user?->id,
            'guest_name' => $user ? null : strip_tags($validated['guest_name']),
            'guest_email' => $user ? null : ($validated['guest_email'] ?? null),
            'content' => $validated['content'],
            'status' => $autoApprove ? 'approved' : 'pending',
        ]);

        return $redirect->with('comment_success', $autoApprove
            ? 'Komentar berhasil ditambahkan.'
            : 'Terima kasih! Komentar Anda akan tampil setelah disetujui moderator.');
    }
}
