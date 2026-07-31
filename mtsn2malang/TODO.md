# TODO - Finalisasi Project MTsN2 Malang
**Update terakhir: 23 Juni 2026**

---

## ✅ SELESAI

### Setup & Infrastruktur
- [x] Perbaiki bug route form login (`route('login')` → `route('login.post')`)
- [x] Validasi keberadaan view yang dirujuk route auth
- [x] Setup lokal Laravel (dependency, env, key, migrate, seed)
- [x] Build assets frontend (Vite → `public/build/` CSS 132KB)
- [x] Uji endpoint utama (`/` 200, `/login` 200, `/admin` 302→login)
- [x] Finalisasi dokumentasi deploy cPanel DomaiNesia (`DEPLOYMENT.md`)
- [x] Deploy ke production & verifikasi berjalan normal

### Auth & Keamanan
- [x] CAPTCHA di `/login` (generate, tampil, refresh AJAX, validasi)
- [x] CAPTCHA di `/admin/login` (Livewire public property, custom view)
- [x] Route `GET /login` → `LoginController@showLogin` (bukan closure)
- [x] Route `GET /captcha/refresh` terdaftar
- [x] OTP 2FA toggle di Pengaturan Website → Tab Keamanan
- [x] `AdminPanelProvider` terdaftar di `bootstrap/providers.php`

### Halaman Publik
- [x] Restore semua halaman publik dari commit `bdc649c9`
- [x] Fix `whistleblowing.blade.php` (konten salah copy-paste)
- [x] Fix `survei/hasil.blade.php` (konten salah copy-paste)
- [x] Fix `laporan.blade.php` — judul & breadcrumb (dari PPID ke Pengaduan)
- [x] Fix `selayang-pandang.blade.php` — judul & breadcrumb benar
- [x] Tambah seksi Riwayat Kepemimpinan di `/tentang/selayang-pandang`
- [x] Fix `gtk-photo` portrait 2:3 (4×6) dengan `rounded-lg`
- [x] Header & footer dinamis via `Setting::get()`
- [x] Favicon & logo dinamis dari Pengaturan Website
- [x] Anti-FOUC dark mode di `app.blade.php`
- [x] Animasi hero banner (zoom in/out) + tombol navigasi custom

### Dashboard Admin
- [x] Restore semua Filament Resources (20 resource)
- [x] Restore Filament Widgets (NewsStats, PageViews, RecentNews)
- [x] Restore `ManageSettings` — 5 tab lengkap
- [x] Tambah `HeadmasterHistoryResource` (Riwayat Kepala Madrasah)
- [x] Tambah `SurveiResource` (lihat respons survei)
- [x] Tambah `SurveyLinkResource`, `AlumniCoordinatorResource`, `SubscriberResource`
- [x] Tambah `SocialMediaContentResource`, `ManageWordpressMigration`
- [x] Restore `UploadImageHelper` (konversi WebP otomatis)
- [x] Restore `ConvertImageToWebpJob`

### Migrasi WordPress
- [x] Refactor halaman menjadi 3 tab (Online, Offline, Rollback)
- [x] Buat `WordpressMigrationService` lengkap dari scratch
- [x] Tambah auto-detect prefix tabel WordPress
- [x] Fix error 500 timeout → dispatch ke Queue Job
- [x] `WordpressMigrationJob` (background, timeout 30 menit)
- [x] Banner status running + auto-refresh 10 detik
- [x] Rollback batch dengan hapus thumbnail dari storage

### Database
- [x] Semua 16 migration sudah `Ran` (0 pending)
- [x] Tabel baru: `headmaster_histories`, `social_media_contents`,
      `alumni_coordinators`, `subscribers`, `survey_links`, `tags`, dll

---

## 🔄 OPSIONAL / LANJUTAN

- [ ] Jalankan queue worker di production untuk migrasi WordPress nyata
      (`php artisan queue:work --timeout=1800 --tries=1`)
- [ ] Isi data awal di admin: GTK, Banner, Kepala Madrasah, Riwayat Kepemimpinan
- [ ] Upload logo & favicon resmi via Pengaturan Website → Tab Umum
- [ ] Isi data Kepala Madrasah via Pengaturan Website → Tab Kepala Madrasah
- [ ] Konfigurasi Gemini API Key untuk fitur generate SEO otomatis di berita
- [ ] Tambahkan cron job di cPanel untuk `php artisan schedule:run`
