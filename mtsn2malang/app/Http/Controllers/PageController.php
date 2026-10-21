<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AlumniCoordinator;
use App\Models\HeadmasterHistory;
use App\Models\Archive;
use App\Models\Category;
use App\Jobs\SendComplaintNotificationJob;
use App\Models\Complaint;
use App\Models\GalleryPost;
use App\Models\Saran;
use App\Models\SchoolLeader;
use App\Models\Extracurricular;
use App\Models\Facility;
use App\Models\Gtk;
use App\Models\News;
use App\Models\PageContent;
use App\Models\PageView;
use App\Models\Program;
use App\Models\Registration;
use App\Models\ServiceStandard;
use App\Models\Setting;
use App\Models\SocialMediaContent;
use App\Models\SurveyLink;
use App\Services\ComplaintNotifier;
use Illuminate\Http\Request;

class PageController extends Controller
{
    // ===== TENTANG KAMI =====
    public function sambutan()
    {
        PageView::record('/tentang/sambutan');
        $content = PageContent::where('page_key', 'sambutan')->first();
        return view('pages.tentang.sambutan', compact('content'));
    }

    public function selayangPandang()
    {
        PageView::record('/tentang/selayang-pandang');
        $content = PageContent::where('page_key', 'selayang_pandang')->first();
        $headmasterHistories = HeadmasterHistory::orderBy('order')->get();
        return view('pages.tentang.selayang-pandang', compact('content', 'headmasterHistories'));
    }

    public function visiMisi()
    {
        PageView::record('/tentang/visi-misi');
        $content = PageContent::where('page_key', 'visi_misi')->first();
        return view('pages.tentang.visi-misi', compact('content'));
    }

    public function strukturMadrasah()
    {
        PageView::record('/tentang/struktur-madrasah');
        $content = PageContent::where('page_key', 'struktur_madrasah')->first();
        $leaders = SchoolLeader::where('is_active', true)->orderBy('order')->get();
        return view('pages.tentang.struktur-madrasah', compact('content', 'leaders'));
    }

    public function strukturKomite()
    {
        PageView::record('/tentang/struktur-komite');
        $content = PageContent::where('page_key', 'struktur_komite')->first();
        return view('pages.tentang.struktur-komite', compact('content'));
    }

    public function daftarGtk()
    {
        PageView::record('/tentang/daftar-gtk');
        $guru = Gtk::where('type', 'guru')->where('is_active', true)->orderBy('order')->get();
        $tendik = Gtk::where('type', 'tendik')->where('is_active', true)->orderBy('order')->get();
        return view('pages.tentang.daftar-gtk', compact('guru', 'tendik'));
    }

    public function ikatanAlumni()
    {
        PageView::record('/tentang/ikatan-alumni');

        $content = PageContent::where('page_key', 'ikatan_alumni')->first();
        $coordinators = AlumniCoordinator::where('is_active', true)
            ->orderBy('order')
            ->get();

        $alumniNews = News::published()->with(['category', 'author'])
            ->withAnyTags(['alumni', 'Alumni'])
            ->latest('published_at')->paginate(9);

        return view('pages.tentang.ikatan-alumni', compact('content', 'coordinators', 'alumniNews'));
    }

    public function ekstrakurikuler()
    {
        PageView::record('/tentang/ekstrakurikuler');
        $ekskul = Extracurricular::where('is_active', true)->orderBy('order')->get();
        $ekskulNews = News::published()->with(['category', 'author'])
            ->latest('published_at')->take(6)->get();
        return view('pages.tentang.ekstrakurikuler', compact('ekskul', 'ekskulNews'));
    }

    public function sarpras()
    {
        PageView::record('/tentang/sarpras');
        $facilities = Facility::where('is_active', true)->orderBy('order')->get();
        return view('pages.tentang.sarpras', compact('facilities'));
    }

    public function kontak()
    {
        PageView::record('/tentang/kontak');
        return view('pages.tentang.kontak');
    }

    // ===== LAYANAN =====
    public function maklumat()
    {
        PageView::record('/layanan/maklumat');
        $content = PageContent::where('page_key', 'maklumat')->first();
        return view('pages.layanan.maklumat', compact('content'));
    }

    public function standarPelayanan()
    {
        PageView::record('/layanan/standar-pelayanan');
        $services = ServiceStandard::where('is_active', true)->orderBy('order')->get();
        return view('pages.layanan.standar-pelayanan', compact('services'));
    }

    public function kompensasi()
    {
        PageView::record('/layanan/kompensasi');
        $content = PageContent::where('page_key', 'kompensasi')->first();
        return view('pages.layanan.kompensasi', compact('content'));
    }

    public function layananPpdb()
    {
        PageView::record('/layanan/ppdb');
        $registration = Registration::where('type', 'ppdb')->where('is_active', true)->first();

        return view('pages.layanan.ppdb', compact('registration'));
    }

    // ===== PROGRAM =====
    public function programUnggulan()
    {
        PageView::record('/program/unggulan');
        $content = \App\Models\PageContent::query()->where('page_key', 'program_unggulan')->first();
        $programs = Program::where('type', 'unggulan')->where('is_active', true)->orderBy('order')->get();
        $news = News::published()->withAnyTags(['Unggulan'])->latest('published_at')->take(6)->get();
        return view('pages.program.unggulan', compact('content', 'programs', 'news'));
    }

    public function programTahfidz()
    {
        PageView::record('/program/tahfidz');
        $content = PageContent::where('page_key', 'kelas_tahfidz')->first();
        return view('pages.program.tahfidz', compact('content'));
    }

    public function programOlimpiade()
    {
        PageView::record('/program/olimpiade');
        $content = PageContent::where('page_key', 'kelas_olimpiade')->first();
        return view('pages.program.olimpiade', compact('content'));
    }

    public function programBilingual()
    {
        PageView::record('/program/bilingual');
        $content = PageContent::where('page_key', 'kelas_bilingual')->first();
        return view('pages.program.bilingual', compact('content'));
    }

    public function programCbi()
    {
        PageView::record('/program/cbi');
        $content = PageContent::where('page_key', 'kelas_cbi')->first();
        return view('pages.program.cbi', compact('content'));
    }

    public function adiwiyata()
    {
        PageView::record('/program/adiwiyata');
        $content = PageContent::where('page_key', 'adiwiyata')->first();
        $news = News::published()->withAnyTags(['Adiwiyata'])->latest('published_at')->take(6)->get();
        return view('pages.program.adiwiyata', compact('content', 'news'));
    }

    public function zonaIntegritas()
    {
        PageView::record('/program/zona-integritas');
        $content = PageContent::where('page_key', 'zona_integritas')->first();
        $news = News::published()->withAnyTags(['Zona Integritas'])->latest('published_at')->take(6)->get();
        return view('pages.program.zona-integritas', compact('content', 'news'));
    }

    // ===== PPID =====
    public function ppidProfil()
    {
        PageView::record('/ppid/profil');
        $content = PageContent::where('page_key', 'ppid_profil')->first();
        return view('pages.ppid.profil', compact('content'));
    }

    /**
     * Daftar arsip dengan filter tahun/bulan + paginasi 20.
     * Dipakai bersama oleh halaman PPID, SAKIP, hasil survei, dan laporan pengaduan.
     */
    /**
     * Ambil parameter query berupa bilangan bulat dalam rentang tertentu.
     * Nilai tidak valid (mis. ?tahun=abc) diabaikan — sebelumnya diteruskan
     * mentah ke PostgreSQL dan menyebabkan error 500.
     */
    private function intQuery(string $name, int $min, int $max): ?int
    {
        $value = filter_var(request()->query($name), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $min, 'max_range' => $max],
        ]);

        return $value === false ? null : $value;
    }

    private function archiveIndex(string $type, string $path, string $view)
    {
        PageView::record($path);

        $query = Archive::ofType($type);

        if ($tahun = $this->intQuery('tahun', 1900, 2100)) {
            $query->whereYear('published_date', $tahun);
        }

        if ($bulan = $this->intQuery('bulan', 1, 12)) {
            $query->whereMonth('published_date', $bulan);
        }

        // withQueryString: filter ikut terbawa saat pindah halaman
        $archives = $query->paginate(20)->withQueryString();

        // Tahun diambil dari data nyata, bukan rentang tebakan
        $years = Archive::where('type', $type)
            ->where('is_active', true)
            ->selectRaw('EXTRACT(YEAR FROM published_date)::int AS y')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y');

        return view($view, compact('archives', 'years'));
    }

    public function ppidBerkala()
    {
        return $this->archiveIndex('berkala', '/ppid/berkala', 'pages.ppid.berkala');
    }

    public function ppidSetiapSaat()
    {
        return $this->archiveIndex('setiap_saat', '/ppid/setiap-saat', 'pages.ppid.setiap-saat');
    }

    public function ppidSertaMerta()
    {
        return $this->archiveIndex('serta_merta', '/ppid/serta-merta', 'pages.ppid.serta-merta');
    }

    public function ppidGaleri()
    {
        PageView::record('/ppid/galeri');

        $query = GalleryPost::with(['photos', 'news'])->whereHas('photos');

        if ($tahun = $this->intQuery('tahun', 1900, 2100)) {
            $query->whereYear('created_at', $tahun);
        }

        if ($bulan = $this->intQuery('bulan', 1, 12)) {
            $query->whereMonth('created_at', $bulan);
        }

        $galleries = $query->latest()->paginate(20)->withQueryString();

        $years = GalleryPost::whereHas('photos')
            ->selectRaw('EXTRACT(YEAR FROM created_at)::int AS y')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y');

        return view('pages.ppid.galeri', compact('galleries', 'years'));
    }

    // ===== SAKIP =====
    public function renstra()
    {
        return $this->archiveIndex('renstra', '/sakip/renstra', 'pages.sakip.renstra');
    }

    public function perkin()
    {
        return $this->archiveIndex('perkin', '/sakip/perkin', 'pages.sakip.perkin');
    }

    public function lkj()
    {
        return $this->archiveIndex('lkj', '/sakip/lkj', 'pages.sakip.lkj');
    }

    // ===== MA'HAD =====
    public function mahadLanding()
    {
        PageView::record('/mahad');
        $latestNews = News::published()->with(['category', 'author'])
            ->withAnyTags(["Ma'had"])
            ->latest('published_at')->take(6)->get();
        $programs = Program::where('type', 'mahad')->where('is_active', true)->orderBy('order')->take(6)->get();
        $facilities = Facility::where('category', 'mahad')->where('is_active', true)->orderBy('order')->take(8)->get();
        $socialMediaContents = SocialMediaContent::where('is_active', true)
            ->where('show_on_mahad', true)
            ->orderBy('order')
            ->take(6)
            ->get();

        return view('pages.mahad.home', compact('latestNews', 'programs', 'facilities', 'socialMediaContents'));
    }

    public function mahadBerita()
    {
        PageView::record('/mahad/berita');
        $news = News::published()->with(['category', 'author'])
            ->withAnyTags(["Ma'had"])
            ->latest('published_at')->paginate(12);
        return view('pages.mahad.berita', compact('news'));
    }

    public function mahadPsb()
    {
        PageView::record('/mahad/psb');
        $registration = Registration::where('type', 'psb')->where('is_active', true)->first();
        $news = News::published()
            ->withAnyTags(["Ma'had"])
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('pages.mahad.psb', compact('registration', 'news'));
    }

    public function mahadProgram()
    {
        PageView::record('/mahad/program');
        $programs = Program::where('type', 'mahad')->where('is_active', true)->orderBy('order')->get();
        $news = News::published()
            ->withAnyTags(["Ma'had"])
            ->latest('published_at')
            ->take(6)
            ->get();
        return view('pages.mahad.program', compact('programs', 'news'));
    }

    public function mahadSarpras()
    {
        PageView::record('/mahad/sarpras');
        $facilities = Facility::where('category', 'mahad')->where('is_active', true)->orderBy('order')->get();
        return view('pages.mahad.sarpras', compact('facilities'));
    }

    public function mahadStruktur()
    {
        PageView::record('/mahad/struktur');
        $content = PageContent::where('page_key', 'struktur_mahad')->first();
        return view('pages.mahad.struktur', compact('content'));
    }

    public function mahadKontak()
    {
        PageView::record('/mahad/kontak');
        return view('pages.mahad.kontak');
    }

    // ===== PENGADUAN =====
    public function whistleblowing()
    {
        PageView::record('/pengaduan/whistleblowing');
        $content = PageContent::where('page_key', 'whistleblowing')->first();
        return view('pages.pengaduan.whistleblowing', compact('content'));
    }

    public function pengaduanMasyarakat()
    {
        PageView::record('/pengaduan/masyarakat');
        return view('pages.pengaduan.masyarakat');
    }

    public function storePengaduan(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'type' => 'required|in:whistleblowing,masyarakat',
        ]);

        $complaint = Complaint::create([
            'type' => $request->type,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'content' => $request->input('content'),
        ]);

        SendComplaintNotificationJob::dispatch($complaint->id, ComplaintNotifier::EVENT_CREATED);

        return back()->with('success', 'Pengaduan berhasil dikirim. Nomor tiket Anda: ' . $complaint->ticket_number);
    }

    public function pelacakanPengaduan(Request $request)
    {
        PageView::record('/pengaduan/pelacakan');
        $complaint = null;
        if ($request->filled('ticket')) {
            $complaint = Complaint::whereIn('type', ['whistleblowing', 'masyarakat'])
                ->where('ticket_number', strtoupper(trim((string) $request->input('ticket'))))
                ->first();
        }
        return view('pages.pengaduan.pelacakan', compact('complaint'));
    }

    public function laporanPengaduan()
    {
        return $this->archiveIndex('laporan_pengaduan', '/pengaduan/laporan', 'pages.pengaduan.laporan');
    }

    // ===== SURVEI =====
    public function surveiForm()
    {
        PageView::record('/survei/form');
        $surveyLinks = SurveyLink::active()->orderBy('order')->orderBy('id')->get();

        return view('pages.survei.form', compact('surveyLinks'));
    }

    public function storeSurvei(Request $request)
    {
        return redirect()
            ->route('survei.form')
            ->with('success', 'Survei saat ini menggunakan link eksternal. Silakan pilih survei pada daftar yang tersedia.');
    }

    public function hasilSurvei()
    {
        return $this->archiveIndex('hasil_survei', '/survei/hasil', 'pages.survei.hasil');
    }

    public function saranForm()
    {
        PageView::record('/survei/saran');
        return view('pages.survei.saran');
    }

    public function lacakSaran(Request $request)
    {
        PageView::record('/survei/saran/lacak');
        $saran = null;
        $notFound = false;

        if ($request->filled('tiket')) {
            // Hanya tiket saran — tiket pengaduan/whistleblowing tidak boleh
            // terbaca lewat halaman ini.
            $saran = Saran::where('type', 'saran')
                ->where('ticket_number', strtoupper(trim((string) $request->input('tiket'))))
                ->first();
            if (!$saran) {
                $notFound = true;
            }
        }

        return view('pages.survei.saran', compact('saran', 'notFound'));
    }

    public function storeSaran(Request $request)
    {
        $request->validate([
            'name'    => 'nullable|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-().]{8,20}$/'],
            'subject' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ], [
            'phone.regex'      => 'Nomor telepon hanya boleh berisi angka (boleh diawali +), minimal 8 digit.',
            'phone.max'        => 'Nomor telepon maksimal 20 karakter.',
            'subject.required' => 'Subjek saran wajib diisi.',
            'content.required' => 'Isi saran wajib diisi.',
            'content.min'      => 'Isi saran minimal 10 karakter.',
        ]);

        $saran = Saran::create([
            'name'    => $request->name ?: 'Anonim',
            'email'   => $request->email,
            'phone'   => filled($request->phone) ? trim((string) $request->phone) : null,
            'subject' => $request->subject,
            'content' => $request->input('content'),
            'status'  => 'pending',
        ]);

        SendComplaintNotificationJob::dispatch($saran->id, ComplaintNotifier::EVENT_CREATED);

        return redirect()
            ->route('survei.saran')
            ->with('success', 'Terima kasih! Saran Anda telah kami terima.')
            ->with('ticket', $saran->ticket_number);
    }

    // ===== ARCHIVE VIEWER =====
    public function viewArchive(int $id)
    {
        // Arsip yang dinonaktifkan admin tidak boleh bisa dibuka lewat ID.
        $archive = Archive::where('is_active', true)->findOrFail($id);

        $archive->incrementViewCount();

        if ($archive->link_type === 'link') {
            abort_unless(preg_match('#^https?://#i', (string) $archive->external_link), 404);

            return redirect()->away($archive->external_link);
        }

        return view('pages.archive-viewer', compact('archive'));
    }
}
