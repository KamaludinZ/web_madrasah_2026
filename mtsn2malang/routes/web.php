<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// ===== HOME =====
Route::get('/', [HomeController::class, 'index'])->name('home');

// ===== BERITA =====
Route::prefix('berita')->name('berita.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('show');
    Route::post('/{id}/komentar', [NewsController::class, 'storeComment'])->name('komentar.store')->middleware('auth');
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
});

// ===== PROGRAM =====
Route::prefix('program')->name('program.')->group(function () {
    Route::get('/unggulan', [PageController::class, 'programUnggulan'])->name('unggulan');
    Route::get('/tahfidz', [PageController::class, 'programTahfidz'])->name('tahfidz');
    Route::get('/olimpiade', [PageController::class, 'programOlimpiade'])->name('olimpiade');
    Route::get('/bilingual', [PageController::class, 'programBilingual'])->name('bilingual');
    Route::get('/cbi', [PageController::class, 'programCbi'])->name('cbi');
    Route::get('/adiwiyata', [PageController::class, 'adiwiyata'])->name('adiwiyata');
});

// ===== PPID =====
Route::prefix('ppid')->name('ppid.')->group(function () {
    Route::get('/berkala', [PageController::class, 'ppidBerkala'])->name('berkala');
    Route::get('/setiap-saat', [PageController::class, 'ppidSetiapSaat'])->name('setiap-saat');
    Route::get('/serta-merta', [PageController::class, 'ppidSertaMerta'])->name('serta-merta');
});

// ===== SAKIP =====
Route::prefix('sakip')->name('sakip.')->group(function () {
    Route::get('/renstra', [PageController::class, 'renstra'])->name('renstra');
    Route::get('/perkin', [PageController::class, 'perkin'])->name('perkin');
    Route::get('/lkj', [PageController::class, 'lkj'])->name('lkj');
});

// ===== MA'HAD =====
Route::prefix('mahad')->name('mahad.')->group(function () {
    Route::get('/berita', [PageController::class, 'mahadBerita'])->name('berita');
    Route::get('/psb', [PageController::class, 'mahadPsb'])->name('psb');
    Route::get('/program', [PageController::class, 'mahadProgram'])->name('program');
    Route::get('/sarpras', [PageController::class, 'mahadSarpras'])->name('sarpras');
    Route::get('/struktur', [PageController::class, 'mahadStruktur'])->name('struktur');
});

// ===== PENGADUAN =====
Route::prefix('pengaduan')->name('pengaduan.')->group(function () {
    Route::get('/whistleblowing', [PageController::class, 'whistleblowing'])->name('whistleblowing');
    Route::post('/whistleblowing', [PageController::class, 'storePengaduan'])->name('whistleblowing.store');
    Route::get('/masyarakat', [PageController::class, 'pengaduanMasyarakat'])->name('masyarakat');
    Route::post('/masyarakat', [PageController::class, 'storePengaduan'])->name('masyarakat.store');
    Route::get('/pelacakan', [PageController::class, 'pelacakanPengaduan'])->name('pelacakan');
    Route::get('/laporan', [PageController::class, 'laporanPengaduan'])->name('laporan');
});

// ===== SURVEI =====
Route::prefix('survei')->name('survei.')->group(function () {
    Route::get('/form', [PageController::class, 'surveiForm'])->name('form');
    Route::post('/form', [PageController::class, 'storeSurvei'])->name('store');
    Route::get('/hasil', [PageController::class, 'hasilSurvei'])->name('hasil');
});

// ===== ARCHIVE VIEWER =====
Route::get('/arsip/{id}/lihat', [PageController::class, 'viewArchive'])->name('archive.view');

// ===== AUTH =====
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');
    Route::post('/otp/verify', [App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])->name('otp.verify');
    Route::get('/forgot-password', fn() => redirect()->route('login')->with('error', 'Silakan hubungi administrator untuk reset password.'))->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});

// ===== KOMENTAR =====
Route::post('/komentar/{id}', [NewsController::class, 'storeComment'])->name('komentar.store')->middleware('auth');
