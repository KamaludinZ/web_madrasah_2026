# Panduan Deploy Ulang ke Production — MTsN 2 Kota Malang
**Versi: 23 Juni 2026 | Commit: fd4cebb4**

> 🐳 **Deploy dengan Docker / Coolify?** Ikuti **[DEPLOY-COOLIFY.md](DEPLOY-COOLIFY.md)**.
> Panduan di bawah ini untuk hosting cPanel (upload file + SSH).

---

## ⚠️ LANGKAH TAMBAHAN — PERBAIKAN HASIL AUDIT (September 2026)

Lakukan **sekali** saat men-deploy versi ini, selain langkah-langkah di bawah:

1. **Hapus dua skrip darurat dari server** (jika masih ada) lewat File Manager:
   `public/db-migrate.php` dan `public/artisan-run.php`. Keduanya bisa dipakai
   siapa pun yang tahu tokennya untuk menjalankan perintah di server.
2. **Ganti password semua akun admin** — password bawaan lama pernah tertulis di
   kode. Password super admin baru untuk instalasi baru diambil dari
   `ADMIN_DEFAULT_PASSWORD` di `.env` (kosong = dibuat acak saat `db:seed`).
3. Jalankan `php artisan migrate --force` (4 migrasi baru: kolom OTP, bio,
   penanda newsletter, dan pelepasan relasi tabel riwayat backup agar restore
   berhasil).
4. Tambahkan ke `.env` server: `SESSION_SECURE_COOKIE=true`.
5. Pastikan **dua** cron job berjalan tiap menit (lihat bagian QUEUE WORKER):
   queue worker **dan** `schedule:run`.
6. **Login admin kini hanya di `/admin/login`** (`/login` otomatis dialihkan).
   Sebelum mengaktifkan OTP di Pengaturan Website → Keamanan, isi tab Email lalu
   pastikan tombol "Kirim Email Tes" berhasil. Jika OTP aktif tetapi email gagal
   terkirim, matikan lewat SSH:
   `php artisan tinker --execute="App\Models\Setting::set('login_otp_enabled', false)"`
7. Password SMTP dan API key AI kini disimpan terenkripsi. Nilai lama tetap
   terbaca; kolom password di form sengaja kosong (kosong = tidak diubah).
8. Upload SVG tidak lagi diterima (bisa berisi skrip). Gunakan PNG/JPG/WEBP.
9. **Komentar pengunjung** kini terbuka tanpa akun: wajib isi nama + kode
   keamanan, dan selalu masuk antrean **Moderasi Komentar** sebelum tampil.
10. **Kode keamanan** (login & komentar) berupa gambar angka. Membutuhkan
    ekstensi PHP GD (umumnya sudah aktif di cPanel); tanpa GD otomatis memakai
    gambar SVG sederhana.
11. **Audit log bersifat permanen**: tidak ada tombol hapus, dan restore backup
    tidak menimpanya. Salinan audit log tetap ikut diarsipkan di file backup
    (`activity_log.sql`).
12. "Total Pengunjung" tetap utuh: detail kunjungan > 90 hari diringkas menjadi
    angka total (jumlah dibaca per berita tidak terpengaruh).

### Menjalankan tes (developer)

Tes memakai database terpisah dan **menolak berjalan** di database lain:

```bash
# sekali saja, di server/komputer developer (BUKAN di production)
psql -U postgres -c "CREATE DATABASE mtsn2malang_testing;"
./vendor/bin/phpunit
```

---

## DAFTAR FILE YANG WAJIB DI-UPLOAD ULANG

Berikut daftar **folder/file yang berubah** dari versi production sebelumnya.
Upload/timpa semua ini ke server hosting.

### ✅ FOLDER — Upload Seluruh Isinya (Timpa)

| Folder | Keterangan |
|--------|------------|
| `app/` | Semua controller, model, Filament resources, jobs, mail, middleware |
| `bootstrap/` | `providers.php` berubah (AdminPanelProvider ditambahkan) |
| `database/migrations/` | 10 migration baru sejak deploy terakhir |
| `database/seeders/` | NewsArchiveSeeder baru |
| `resources/views/` | Semua view halaman publik & admin diperbaiki |
| `resources/css/` | `app.css` — class baru gtk-photo portrait, hm-card timeline |
| `resources/js/` | JS utama |
| `routes/` | `web.php` — route baru (subscription, mahad, ppdb) |
| `public/build/` | **WAJIB** — hasil build terbaru (hapus isi `public/build/assets` lama di server dulu) |
| `public/css/` | Filament CSS assets |
| `public/js/` | Filament JS assets |
| `public/images/` | favicon, apple-touch-icon, logo & semua gambar placeholder, og-default.jpg |
| `public/robots.txt` | Petunjuk mesin pencari + lokasi sitemap |

### ✅ FILE INDIVIDUAL — Upload/Timpa

| File | Keterangan |
|------|------------|
| `public/.htaccess` | Force HTTPS + security headers + gzip |
| `public/.user.ini` | Naikkan batas upload PHP (200MB) — dibutuhkan fitur Backup/Restore/Update. Hanya berlaku di PHP-FPM; kalau hosting pakai mod_php, atur manual lewat cPanel "MultiPHP INI Editor" |
| `public/index.php` | Entry point Laravel |
| `vite.config.js` | Konfigurasi Vite diperbarui |
| `postcss.config.js` | File baru |
| `composer.json` | Dependency list |
| `composer.lock` | Lock file dependency |
| `tailwind.config.js` | Konfigurasi warna & font |
| `VERSION` | Nomor versi terpasang — dibaca fitur Update Aplikasi di `/admin/manage-settings` |

### ❌ JANGAN UPLOAD / JANGAN TIMPA

| File/Folder | Alasan |
|-------------|--------|
| `.env` | Edit manual di server — berisi kredensial production |
| `vendor/` | Jalankan `composer install` di server |
| `node_modules/` | Tidak dibutuhkan di production |
| `storage/` | Berisi upload user — jangan ditimpa |
| `public/storage` | Symlink — buat ulang via artisan jika rusak |
| `public/hot` | File dev Vite — JANGAN ada di production |

---

## LANGKAH EKSEKUSI DI SERVER (via SSH / Terminal cPanel)

Jalankan **berurutan** setelah semua file di-upload:

```bash
# Masuk ke direktori project
cd /path/to/project   # sesuaikan dengan path di hosting Anda

# LANGKAH 1 — Install/update PHP dependencies
composer install --optimize-autoloader --no-dev

# LANGKAH 2 — Jalankan semua migration baru
# (10 tabel baru: social_media_contents, alumni_coordinators, subscribers,
#  survey_links, tags, wordpress_migration_batches, headmaster_histories, dll)
php artisan migrate --force

# LANGKAH 3 — Pastikan storage symlink ada
php artisan storage:link

# LANGKAH 4 — Publish Filament assets (CSS/JS admin panel)
php artisan filament:assets

# LANGKAH 5 — Clear semua cache lama
php artisan optimize:clear

# LANGKAH 6 — Cache ulang untuk production (performa lebih cepat)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# LANGKAH 7 — Set permission (jika diperlukan)
chmod -R 755 storage bootstrap/cache
```

---

## QUEUE WORKER (WAJIB untuk fitur Migrasi WordPress, Backup, Restore, dan Update Aplikasi)

Fitur-fitur di atas berjalan sebagai job antrian (`QUEUE_CONNECTION=database`) supaya
tidak timeout di request HTTP biasa. Tanpa worker berjalan, job hanya akan
tersimpan di tabel `jobs` dan **tidak pernah diproses** — tombol "Buat Backup",
"Restore", dan "Update" akan terlihat macet di status "Berjalan" selamanya.

Cara termudah di cPanel: buat **Cron Job** yang jalan tiap menit —

```bash
* * * * * cd /path/to/project && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Baris kedua menjalankan tugas terjadwal (mis. pembersihan data kunjungan lebih
dari 90 hari). Newsletter berita baru juga dikirim lewat antrian ini.

Catatan: `DB_QUEUE_RETRY_AFTER` (default 2000 detik) harus lebih lama dari job
terpanjang (30 menit). Jangan diturunkan, atau job backup/restore/update yang
masih berjalan bisa diambil ulang worker lain lalu ditandai gagal.

`--stop-when-empty` membuat worker keluar sendiri setelah antrian kosong, jadi
aman dijalankan berulang tiap menit tanpa numpuk proses. Kalau hosting punya
Supervisor/Process Manager, `php artisan queue:work --tries=1` yang selalu
hidup adalah pilihan yang lebih baik.

---

## KONFIGURASI .env DI SERVER

Edit file `.env` di server (JANGAN timpa dengan .env lokal).
Pastikan nilai berikut sudah benar:

```env
APP_ENV=production
APP_DEBUG=false                          # ← WAJIB false
APP_URL=https://mtsn2kotamalang.sch.id   # ← sesuaikan domain

SESSION_DOMAIN=mtsn2kotamalang.sch.id    # ← sesuaikan domain
SESSION_SECURE_COOKIE=true               # ← true karena HTTPS

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nama_db_production           # ← sesuaikan
DB_USERNAME=user_db_production           # ← sesuaikan
DB_PASSWORD=password_db_production       # ← sesuaikan
```

---

## MIGRATION BARU YANG AKAN DIJALANKAN

`php artisan migrate --force` akan menjalankan migration berikut
(yang belum ada di production lama):

| Migration | Tabel/Perubahan |
|-----------|-----------------|
| `2026_06_03_015606` | `social_media_contents` |
| `2026_06_17_000001` | `alumni_coordinators` |
| `2026_06_18_000002` | alter `programs` (tambah kolom `link`) |
| `2026_06_18_000003` | `subscribers` |
| `2026_06_18_074242` | `wordpress_migration_batches` |
| `2026_06_18_074243` | alter `news` (tambah `migration_batch_id`) |
| `2026_06_22_050549` | `survey_links` |
| `2026_06_22_103900` | alter `social_media_contents` (tambah `show_on_mahad`) |
| `2026_06_22_110500` | `tags`, `taggables` |
| `2026_06_23_000001` | `headmaster_histories` |

---

## CHECKLIST VERIFIKASI SETELAH DEPLOY

Buka browser dan cek satu per satu:

**Halaman Publik:**
- [ ] `https://domain/` — Homepage, hero slider animasi zoom, tombol nav custom
- [ ] `https://domain/berita` — Daftar berita
- [ ] `https://domain/tentang/sambutan` — Foto kepala madrasah dinamis
- [ ] `https://domain/tentang/selayang-pandang` — Konten + timeline riwayat kepemimpinan
- [ ] `https://domain/tentang/daftar-gtk` — Foto portrait 4×6 dengan watermark
- [ ] `https://domain/tentang/struktur-madrasah` — Konten benar (bukan visi misi)
- [ ] `https://domain/tentang/ekstrakurikuler` — Konten benar
- [ ] `https://domain/layanan/standar-pelayanan` — Tabel standar pelayanan
- [ ] `https://domain/pengaduan/whistleblowing` — Form whistleblowing
- [ ] `https://domain/pengaduan/laporan` — Judul "Laporan Hasil Pengaduan"
- [ ] `https://domain/survei/form` — Link survei eksternal
- [ ] `https://domain/mahad` — Landing page Ma'had

**Dashboard Admin (`https://domain/admin`):**
- [ ] Login berhasil → dashboard dengan 3 widget
- [ ] **Profil Sekolah → Riwayat Kepala** — menu baru untuk riwayat kepemimpinan
- [ ] **Pengaduan & Survei → Link Survei** — manajemen link survei eksternal
- [ ] **Pengaturan Website** — 5 tab: Umum, Header & Footer, Kepala Madrasah, AI & SEO, Keamanan
- [ ] Upload foto di Banner/GTK/Kepala Madrasah → otomatis konversi WebP

---

## TROUBLESHOOTING

### Tampilan polos / CSS tidak load
```bash
# Pastikan public/hot TIDAK ADA
ls public/hot   # jika ada, hapus: rm public/hot

# Pastikan public/build/ sudah di-upload
ls public/build/assets/   # harus ada app-Ddtruj7f.css

php artisan optimize:clear
```

### Error 500 setelah deploy
```bash
# Cek log
tail -50 storage/logs/laravel.log

# Clear cache
php artisan optimize:clear
php artisan config:cache
```

### Filament admin tampilan berantakan
```bash
php artisan filament:assets
# Pastikan public/css/filament/ dan public/js/filament/ sudah di-upload
```

### Migration error
```bash
php artisan migrate:status   # cek status tiap migration
php artisan migrate --force  # jalankan ulang
```

### Gambar tidak muncul
```bash
php artisan storage:link
chmod -R 755 storage/app/public
```

### Login redirect loop
```bash
# Pastikan di .env:
# SESSION_DOMAIN=domain.sch.id (tanpa https://)
# SESSION_SECURE_COOKIE=true
php artisan config:cache
```
