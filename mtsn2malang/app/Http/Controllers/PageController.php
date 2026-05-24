<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Complaint;
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
        return view('pages.tentang.selayang-pandang', compact('content'));
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
        return view('pages.tentang.struktur-madrasah', compact('content'));
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
        $alumniNews = News::published()->with(['category', 'author'])
            ->whereHas('tags', fn($q) => $q->where('name->id', 'alumni'))
            ->latest('published_at')->paginate(9);
        return view('pages.tentang.ikatan-alumni', compact('content', 'alumniNews'));
    }

    public function ekstrakurikuler()
    {
        PageView::record('/tentang/ekstrakurikuler');
        $ekskul = Extracurricular::where('is_active', true)->orderBy('order')->get();
        $ekskulNews = News::published()->with(['category', 'author'])
            ->whereHas('tags', fn($q) => $q->where('name->id', 'ekstrakurikuler'))
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

    // ===== PROGRAM =====
    public function programUnggulan()
    {
        PageView::record('/program/unggulan');
        $programs = Program::where('type', 'unggulan')->where('is_active', true)->orderBy('order')->get();
        $news = News::published()->whereHas('tags', fn($q) => $q->where('name->id', 'unggulan'))->latest('published_at')->take(6)->get();
        return view('pages.program.unggulan', compact('programs', 'news'));
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
        $news = News::published()->whereHas('tags', fn($q) => $q->where('name->id', 'adiwiyata'))->latest('published_at')->take(6)->get();
        return view('pages.program.adiwiyata', compact('content', 'news'));
    }

    // ===== PPID =====
    public function ppidBerkala()
    {
        PageView::record('/ppid/berkala');
        $archives = Archive::ofType('berkala')->paginate(12);
        return view('pages.ppid.berkala', compact('archives'));
    }

    public function ppidSetiapSaat()
    {
        PageView::record('/ppid/setiap-saat');
        $archives = Archive::ofType('setiap_saat')->paginate(12);
        return view('pages.ppid.setiap-saat', compact('archives'));
    }

    public function ppidSertaMerta()
    {
        PageView::record('/ppid/serta-merta');
        $archives = Archive::ofType('serta_merta')->paginate(12);
        return view('pages.ppid.serta-merta', compact('archives'));
    }

    // ===== SAKIP =====
    public function renstra()
    {
        PageView::record('/sakip/renstra');
        $archives = Archive::ofType('renstra')->paginate(12);
        return view('pages.sakip.renstra', compact('archives'));
    }

    public function perkin()
    {
        PageView::record('/sakip/perkin');
        $archives = Archive::ofType('perkin')->paginate(12);
        return view('pages.sakip.perkin', compact('archives'));
    }

    public function lkj()
    {
        PageView::record('/sakip/lkj');
        $archives = Archive::ofType('lkj')->paginate(12);
        return view('pages.sakip.lkj', compact('archives'));
    }

    // ===== MA'HAD =====
    public function mahadBerita()
    {
        PageView::record('/mahad/berita');
        $news = News::published()->with(['category', 'author'])
            ->whereHas('tags', fn($q) => $q->where('name->id', 'mahad'))
            ->latest('published_at')->paginate(12);
        return view('pages.mahad.berita', compact('news'));
    }

    public function mahadPsb()
    {
        PageView::record('/mahad/psb');
        $registration = Registration::where('type', 'psb')->where('is_active', true)->first();
        $news = News::published()->whereHas('tags', fn($q) => $q->where('name->id', 'psb'))->latest('published_at')->take(6)->get();
        return view('pages.mahad.psb', compact('registration', 'news'));
    }

    public function mahadProgram()
    {
        PageView::record('/mahad/program');
        $programs = Program::where('type', 'mahad')->where('is_active', true)->orderBy('order')->get();
        $news = News::published()->whereHas('tags', fn($q) => $q->where('name->id', 'mahad'))->latest('published_at')->take(6)->get();
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

    // ===== PENGADUAN =====
    public function whistleblowing()
    {
        PageView::record('/pengaduan/whistleblowing');
        return view('pages.pengaduan.whistleblowing');
    }

    public function pengaduanMasyarakat()
    {
        PageView::record('/pengaduan/masyarakat');
        return view('pages.pengaduan.masyarakat');
    }

    public function storePengaduan(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:whistleblowing,masyarakat',
        ]);

        $complaint = Complaint::create([
            'type' => $request->type,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Pengaduan berhasil dikirim. Nomor tiket Anda: ' . $complaint->ticket_number);
    }

    public function pelacakanPengaduan(Request $request)
    {
        PageView::record('/pengaduan/pelacakan');
        $complaint = null;
        if ($request->filled('ticket')) {
            $complaint = Complaint::where('ticket_number', $request->ticket)->first();
        }
        return view('pages.pengaduan.pelacakan', compact('complaint'));
    }

    public function laporanPengaduan()
    {
        PageView::record('/pengaduan/laporan');
        $archives = Archive::ofType('laporan_pengaduan')->paginate(12);
        return view('pages.pengaduan.laporan', compact('archives'));
    }

    // ===== SURVEI =====
    public function surveiForm()
    {
        PageView::record('/survei/form');
        return view('pages.survei.form');
    }

    public function storeSurvei(Request $request)
    {
        $request->validate([
            'type' => 'required|in:skm,spk',
            'answers' => 'required|array',
        ]);

        \App\Models\SurveyResponse::create([
            'type' => $request->type,
            'respondent_name' => $request->respondent_name,
            'respondent_type' => $request->respondent_type,
            'answers' => $request->answers,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Terima kasih! Survei Anda telah berhasil dikirim.');
    }

    public function hasilSurvei()
    {
        PageView::record('/survei/hasil');
        $archives = Archive::ofType('hasil_survei')->paginate(12);
        return view('pages.survei.hasil', compact('archives'));
    }

    // ===== ARCHIVE VIEWER =====
    public function viewArchive(int $id)
    {
        $archive = Archive::findOrFail($id);
        $archive->incrementViewCount();

        if ($archive->link_type === 'link') {
            return redirect()->away($archive->external_link);
        }

        return view('pages.archive-viewer', compact('archive'));
    }
}
