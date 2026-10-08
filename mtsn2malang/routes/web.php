<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ===== HOME =====
// Halaman Portal = landing page di "/". Beranda lama pindah ke "/beranda"
// (nama route 'home' tetap, jadi semua tautan "Beranda" ikut berpindah).
Route::get('/', [App\Http\Controllers\PortalController::class, 'index'])->name('portal');
Route::get('/beranda', [HomeController::class, 'index'])->name('home');
Route::redirect('/portal', '/', 301);

// ===== SEO =====
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// ===== BERITA =====
Route::prefix('berita')->name('berita.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('show');
});

// ===== PENULIS =====
Route::get('/penulis/{username}', [NewsController::class, 'byAuthor'])->name('penulis.show');

// ===== PENCARIAN =====
Route::prefix('pencarian')->name('search.')->group(function () {
    Route::get('/', [SearchController::class, 'index'])->name('index');
    Route::get('/ajax', [SearchController::class, 'ajax'])->name('ajax');
});

// ===== TENTANG KAMI =====
Route::prefix('tentang')->name('tentang.')->group(function () {
    Route::get('/sambutan', [PageController::class, 'sambutan'])->name('sambutan');
    Route::get('/selayang-pandang', [PageController::class, 'selayangPandang'])->name('selayang-pandang');
    Route::get('/visi-misi', [PageController::class, 'visiMisi'])->name('visi-misi');
    Route::get('/struktur-madrasah', [PageController::class, 'strukturMadrasah'])->name('struktur-madrasah');
    Route::get('/struktur-komite', [PageController::class, 'strukturKomite'])->name('struktur-komite');
    Route::get('/daftar-gtk', [PageController::class, 'daftarGtk'])->name('daftar-gtk');
    Route::get('/ikatan-alumni', [PageController::class, 'ikatanAlumni'])->name('ikatan-alumni');
    Route::get('/ekstrakurikuler', [PageController::class, 'ekstrakurikuler'])->name('ekstrakurikuler');
    Route::get('/sarpras', [PageController::class, 'sarpras'])->name('sarpras');
    Route::get('/kontak', [PageController::class, 'kontak'])->name('kontak');
});

// ===== LAYANAN =====
Route::prefix('layanan')->name('layanan.')->group(function () {
    Route::get('/maklumat', [PageController::class, 'maklumat'])->name('maklumat');
    Route::get('/standar-pelayanan', [PageController::class, 'standarPelayanan'])->name('standar-pelayanan');
    Route::get('/kompensasi', [PageController::class, 'kompensasi'])->name('kompensasi');
    Route::get('/ppdb', [PageController::class, 'layananPpdb'])->name('ppdb');
});

// ===== PROGRAM =====
Route::prefix('program')->name('program.')->group(function () {
    Route::get('/unggulan', [PageController::class, 'programUnggulan'])->name('unggulan');
    Route::get('/tahfidz', [PageController::class, 'programTahfidz'])->name('tahfidz');
    Route::get('/olimpiade', [PageController::class, 'programOlimpiade'])->name('olimpiade');
    Route::get('/bilingual', [PageController::class, 'programBilingual'])->name('bilingual');
    Route::get('/cbi', [PageController::class, 'programCbi'])->name('cbi');
    Route::get('/adiwiyata', [PageController::class, 'adiwiyata'])->name('adiwiyata');
    Route::get('/zona-integritas', [PageController::class, 'zonaIntegritas'])->name('zona-integritas');
});

// ===== PPID =====
Route::prefix('ppid')->name('ppid.')->group(function () {
    Route::get('/profil', [PageController::class, 'ppidProfil'])->name('profil');
    Route::get('/berkala', [PageController::class, 'ppidBerkala'])->name('berkala');
    Route::get('/setiap-saat', [PageController::class, 'ppidSetiapSaat'])->name('setiap-saat');
    Route::get('/serta-merta', [PageController::class, 'ppidSertaMerta'])->name('serta-merta');
    Route::get('/galeri', [PageController::class, 'ppidGaleri'])->name('galeri');
});

// ===== SAKIP =====
Route::prefix('sakip')->name('sakip.')->group(function () {
    Route::get('/renstra', [PageController::class, 'renstra'])->name('renstra');
    Route::get('/perkin', [PageController::class, 'perkin'])->name('perkin');
    Route::get('/lkj', [PageController::class, 'lkj'])->name('lkj');
});

Route::get('/mahad', [PageController::class, 'mahadLanding'])->name('mahad.home');


// ===== MA'HAD =====
Route::prefix('mahad')->name('mahad.')->group(function () {
    Route::get('/berita', [PageController::class, 'mahadBerita'])->name('berita');
    Route::get('/psb', [PageController::class, 'mahadPsb'])->name('psb');
    Route::get('/program', [PageController::class, 'mahadProgram'])->name('program');
    Route::get('/sarpras', [PageController::class, 'mahadSarpras'])->name('sarpras');
    Route::get('/struktur', [PageController::class, 'mahadStruktur'])->name('struktur');
    Route::get('/kontak', [PageController::class, 'mahadKontak'])->name('kontak');
});

// ===== PENGADUAN =====
Route::prefix('pengaduan')->name('pengaduan.')->group(function () {
    Route::get('/whistleblowing', [PageController::class, 'whistleblowing'])->name('whistleblowing');
    Route::post('/whistleblowing', [PageController::class, 'storePengaduan'])->name('whistleblowing.store')->middleware('throttle:5,10');
    Route::get('/masyarakat', [PageController::class, 'pengaduanMasyarakat'])->name('masyarakat');
    Route::post('/masyarakat', [PageController::class, 'storePengaduan'])->name('masyarakat.store')->middleware('throttle:5,10');
    Route::get('/pelacakan', [PageController::class, 'pelacakanPengaduan'])->name('pelacakan')->middleware('throttle:20,1');
    Route::get('/laporan', [PageController::class, 'laporanPengaduan'])->name('laporan');
});

// ===== SURVEI =====
Route::prefix('survei')->name('survei.')->group(function () {
    Route::get('/form', [PageController::class, 'surveiForm'])->name('form');
    Route::post('/form', [PageController::class, 'storeSurvei'])->name('store')->middleware('throttle:10,10');
    Route::get('/hasil', [PageController::class, 'hasilSurvei'])->name('hasil');
    Route::get('/saran', [PageController::class, 'saranForm'])->name('saran');
    Route::post('/saran', [PageController::class, 'storeSaran'])->name('saran.store')->middleware('throttle:5,10');
    Route::get('/saran/lacak', [PageController::class, 'lacakSaran'])->name('saran.lacak')->middleware('throttle:20,1');
});

// ===== ARCHIVE VIEWER =====
Route::get('/arsip/{id}/lihat', [PageController::class, 'viewArchive'])->name('archive.view');

// ===== AUTH =====
// Login hanya lewat panel admin (/admin/login) yang sudah memuat kode keamanan
// dan OTP. /login lama dipertahankan sebagai alamat pintas (dan sebagai nama
// route 'login' yang dipakai middleware auth).
Route::redirect('/login', '/admin/login')->name('login');

// Gambar kode keamanan (angka) — setiap permintaan membuat kode baru.
Route::get('/kode-keamanan/{context}', [App\Http\Controllers\CaptchaController::class, 'image'])
    ->whereIn('context', App\Support\NumericCaptcha::CONTEXTS)
    ->middleware('throttle:60,1')
    ->name('captcha.image');

// Unduh backup (dialirkan dari disk). URL bertanda tangan & berumur pendek,
// dibuat dari Pengaturan Website → Backup; tetap wajib login super_admin.
Route::get('/admin/backup/{backup}/unduh', App\Http\Controllers\BackupDownloadController::class)
    ->middleware(['auth', 'signed'])
    ->name('backup.download');

// Upload file backup besar untuk restore secara bertahap (per potongan).
Route::post('/admin/restore/unggah-bagian', App\Http\Controllers\RestoreChunkUploadController::class)
    ->middleware(['auth', 'throttle:600,1'])
    ->name('restore.chunk-upload');

// ===== SUBSCRIPTION =====
Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store')->middleware('throttle:5,10');
Route::get('/subscription/unsubscribe/{token}', [SubscriptionController::class, 'unsubscribe'])->name('subscription.unsubscribe');

// ===== KOMENTAR =====
// Pengunjung tanpa akun boleh berkomentar; komentar tamu wajib dimoderasi.
Route::post('/komentar/{id}', [NewsController::class, 'storeComment'])
    ->whereNumber('id')
    ->name('komentar.store')
    ->middleware('throttle:5,10');
