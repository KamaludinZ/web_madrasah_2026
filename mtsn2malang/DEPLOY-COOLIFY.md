# Panduan Deploy ke Coolify (Docker) — MTsN 2 Kota Malang

Panduan ini untuk menjalankan website di server sendiri memakai
[Coolify](https://coolify.io) (v4). Semua file yang dibutuhkan sudah ada di
repo: `Dockerfile`, `docker-compose.yml`, dan folder `docker/`.

> Deploy lama lewat cPanel/FTP (lihat `DEPLOYMENT.md`) tetap bisa dipakai.
> Panduan ini khusus untuk Docker/Coolify.

---

## 1. Gambaran singkat

Coolify akan menjalankan **4 container** dari satu repo:

| Container   | Tugas                                                                 |
|-------------|-----------------------------------------------------------------------|
| `app`       | Website & panel admin (nginx + PHP). Menjalankan migrasi otomatis.    |
| `worker`    | Proses latar belakang: backup, restore, migrasi WordPress, newsletter. |
| `scheduler` | Tugas terjadwal (mis. meringkas data kunjungan setiap hari).          |
| `db`        | Database PostgreSQL 16.                                               |

Dan **2 volume** (penyimpanan permanen, tidak hilang saat redeploy):

| Volume        | Isi                                                               |
|---------------|-------------------------------------------------------------------|
| `app-storage` | Foto/berkas upload, file backup, log, sesi, cache.                |
| `db-data`     | Isi database.                                                     |

Hal yang terjadi **otomatis** setiap kali container `app` menyala:
1. Menyiapkan folder penyimpanan dan hak aksesnya.
2. Menunggu database siap.
3. Menjalankan migrasi database (`migrate --force`).
4. Jika database masih kosong (deploy pertama): membuat akun super admin
   dan data awal. **Password admin tercetak di log** (lihat langkah 4).
5. Menyusun cache agar website cepat, lalu menyalakan nginx + PHP.

---

## 2. Persiapan

1. **Server dengan Coolify v4** sudah terpasang (minimal 2 GB RAM disarankan).
2. **Domain** (mis. `mtsn2kotamalang.sch.id`) diarahkan ke IP server:
   buat record DNS **A** → IP server Coolify. Tunggu hingga aktif.
3. **Akses GitHub**: repo ini privat, jadi di Coolify tambahkan GitHub App:
   menu **Sources → + Add → GitHub App**, lalu izinkan akses ke repo
   `KamaludinZ/web_madrasah_2026`.
4. (Jika pindah dari hosting lama) **catat nilai `APP_KEY`** dari file `.env`
   di server lama — dibutuhkan agar password email & data terenkripsi lain
   tetap terbaca (lihat bagian 6).

---

## 3. Membuat aplikasi di Coolify

1. Buka **Projects** → pilih/buat project → **+ Add New Resource**.
2. Pilih **Private Repository (with GitHub App)**, lalu pilih repo
   `web_madrasah_2026` dan branch **`main`**.
3. Pada pilihan **Build Pack**, pilih **Docker Compose**.
4. Isi:
   - **Base Directory**: `/mtsn2malang`
   - **Docker Compose Location**: `/docker-compose.yml`
5. Klik **Continue / Save**. Coolify membaca `docker-compose.yml` dan
   menampilkan 4 service: `app`, `worker`, `scheduler`, `db`.

### 3a. Domain

Pada service **`app`**, isi kolom **Domains** dengan domain Anda **ditambah
`:8080`** di belakangnya (port di dalam container, bukan port publik):

```
https://mtsn2kotamalang.sch.id:8080
```

Coolify akan otomatis membuat sertifikat HTTPS (Let's Encrypt) dan
pengunjung tetap membuka `https://mtsn2kotamalang.sch.id` seperti biasa.
Service `worker`, `scheduler`, dan `db` **tidak** diberi domain.

Ingin juga `www.`? Pisahkan dengan koma:
`https://mtsn2kotamalang.sch.id:8080,https://www.mtsn2kotamalang.sch.id:8080`

### 3b. Environment Variables

Buka tab **Environment Variables** dan isi:

| Variabel                 | Wajib? | Isi                                                                 |
|--------------------------|--------|---------------------------------------------------------------------|
| `APP_URL`                | **Ya** | Alamat situs, mis. `https://mtsn2kotamalang.sch.id` (tanpa `:8080`) |
| `APP_KEY`                | Sangat disarankan | Lihat cara membuat di bawah                               |
| `ADMIN_DEFAULT_PASSWORD` | Tidak  | Password super admin saat deploy pertama. Kosong = dibuat acak.     |
| `DB_DATABASE`            | Tidak  | Nama database. Default `mtsn2malang`.                               |
| `MAINTENANCE_SECRET`     | Tidak  | Kata rahasia URL bypass mode maintenance.                           |
| `LOG_LEVEL`              | Tidak  | Default `info`. Isi `debug` hanya saat mencari masalah.             |

`SERVICE_USER_POSTGRES` dan `SERVICE_PASSWORD_POSTGRES` **dibuat otomatis**
oleh Coolify — jangan diubah setelah deploy pertama (database memakai
password itu).

**Cara membuat `APP_KEY`:**
- **Pindah dari hosting lama?** Pakai nilai `APP_KEY` yang sama persis dari
  `.env` server lama (formatnya `base64:....`).
- **Instalasi baru?** Boleh dikosongkan dulu. Saat deploy pertama, container
  membuat kunci sendiri dan menulisnya di log container `app`:
  ```
  [entrypoint:app] PERINGATAN: APP_KEY=base64:xxxxxxxxxxxx
  ```
  **Salin nilai itu ke variabel `APP_KEY` di Coolify**, lalu simpan.
  (Kunci juga tersimpan di volume, tetapi menyimpannya di Coolify lebih aman.)

> ⚠️ `APP_KEY` tidak boleh berubah setelah website dipakai: kunci ini
> mengenkripsi sesi login, password SMTP, dan API key AI di Pengaturan.

---

## 4. Deploy

1. Klik **Deploy**. Build pertama memakan waktu ±5–10 menit.
2. Buka **Logs** service **`app`**. Deploy pertama yang sukses terlihat seperti:
   ```
   [entrypoint:app] Database siap.
   [entrypoint:app] Menjalankan migrasi database...
   [entrypoint:app] Database baru (belum ada pengguna) — menjalankan seeder awal...
   Admin credentials:
   Email: admin@mtsn2kotamalang.sch.id
   Password: aRN7EUISp0Yia7F6
   [entrypoint:app] Siap. Menjalankan: supervisord ...
   ```
   **Catat password admin** tersebut (hanya muncul sekali).
3. Tunggu sampai status keempat service **Running (healthy)**.
4. Buka `https://mtsn2kotamalang.sch.id` dan
   `https://mtsn2kotamalang.sch.id/admin/login`.

### Setelah login pertama
1. **Ganti password** super admin (menu profil kanan atas → Profil Saya).
2. **Pengaturan Website → Email**: isi SMTP, simpan, lalu klik
   **Kirim Email Tes** sampai berhasil.
3. Baru setelah email berhasil, aktifkan **OTP** di tab **Keamanan** (opsional).
4. Tab **Update Aplikasi** di panel memang **dinonaktifkan** di Docker —
   update dilakukan lewat GitHub (bagian 7).

---

## 5. Cek kesehatan cepat

| Yang dicek                         | Cara                                                            |
|------------------------------------|-----------------------------------------------------------------|
| Website hidup                      | Buka `https://domain/up` → halaman hijau "Application up".      |
| Worker jalan                       | Panel → Pengaturan Website → **Backup & Restore** → **Buat Backup Sekarang**. Status harus berubah jadi *Selesai* dalam ±1 menit. |
| Upload gambar                      | Tambah berita dengan gambar → gambar tampil di halaman berita.  |
| Komentar & kode keamanan           | Kirim komentar sebagai pengunjung → muncul di Moderasi Komentar. |

---

## 6. Memindahkan data dari hosting lama (cPanel)

### Cara A — lewat fitur Backup & Restore (paling mudah)

Syarat: ukuran file backup ≤ 190 MB.

1. **Di server lama**: pastikan `APP_KEY` lama sudah diisi di Coolify
   (bagian 3b) **sebelum** deploy pertama.
2. Di panel admin **server lama** → Pengaturan Website → **Backup & Restore**
   → **Buat Backup Sekarang** → setelah selesai, klik ikon **Unduh** pada daftar backup.
   (Butuh cron queue worker di hosting lama — lihat `DEPLOYMENT.md`. Jika tombol
   macet di "Berjalan", pakai Cara B.)
3. Deploy di Coolify seperti biasa (bagian 3–4), login dengan password dari log.
4. Di panel **server baru** → Pengaturan Website → **Backup & Restore** → pilih file ZIP →
   ketik `RESTORE` → jalankan. Tunggu sampai status *Selesai*.
5. **Restart service `app`** di Coolify (tombol Restart) agar migrasi terbaru
   dijalankan ulang di atas data lama. Login kini memakai akun & password
   dari server lama.

### Cara B — manual (untuk data besar / fitur backup tidak jalan)

1. **Database**: di server lama, ekspor database ke file SQL, mis. lewat SSH:
   ```bash
   pg_dump --no-owner --no-privileges -h localhost -U USER_DB NAMA_DB > data.sql
   ```
2. **File upload**: kompres folder `storage/app/public` di server lama
   (mis. `tar czf uploads.tar.gz -C storage/app public`).
3. Di server Coolify (SSH ke server), salin keduanya ke container:
   ```bash
   # Cari nama container (mengandung "app-" dan "db-")
   docker ps --format '{{.Names}}' | grep -E 'app-|db-'

   # Database (container db): kosongkan lalu impor
   docker exec -i NAMA_CONTAINER_DB psql -U USER -d mtsn2malang -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"
   docker exec -i NAMA_CONTAINER_DB psql -U USER -d mtsn2malang < data.sql

   # File upload (container app)
   docker cp uploads.tar.gz NAMA_CONTAINER_APP:/tmp/
   docker exec NAMA_CONTAINER_APP sh -c "tar xzf /tmp/uploads.tar.gz -C /var/www/html/storage/app && chown -R www-data:www-data /var/www/html/storage/app/public"
   ```
   `USER` = nilai `SERVICE_USER_POSTGRES` di Coolify.
4. **Restart service `app`** agar migrasi dijalankan di atas data lama.

---

## 7. Operasional sehari-hari

### Update aplikasi
1. Perubahan kode di-merge ke branch **`main`** di GitHub.
2. Coolify membangun & menjalankan versi baru:
   - otomatis bila **Auto Deploy** aktif (tab *Webhooks/General* di Coolify
     — aktif secara default untuk GitHub App), atau
   - klik **Redeploy** secara manual.
3. Migrasi database berjalan otomatis. Data (volume) tidak tersentuh.

### Menjalankan perintah artisan
Coolify → service **`app`** → **Terminal**, lalu gunakan **`app-artisan`**
(bukan `php artisan`, karena terminal masuk sebagai root dan bisa membuat
file yang tidak bisa ditulis website):

```bash
app-artisan migrate:status
app-artisan optimize:clear
app-artisan tinker
# Contoh: matikan OTP bila email bermasalah dan admin tidak bisa login
app-artisan tinker --execute="App\Models\Setting::set('login_otp_enabled', false)"
```

### Log
- Coolify → pilih service → **Logs** (paling lengkap).
- Panel admin → Pengaturan Website → tab **Monitoring Log**.

### Backup
- Panel admin → Pengaturan Website → **Backup & Restore** → **Buat Backup Sekarang**, lalu **Unduh** dan
  simpan di tempat lain (Google Drive/komputer) secara berkala. File backup
  di server tersimpan di volume `app-storage`.
- Backup berisi database + semua file upload. Audit log ikut diarsipkan.

### Mode maintenance
Panel admin → menu **Mode Maintenance** (grup Pengaturan). Panel admin tetap bisa
dibuka selama maintenance.

---

## 8. Mengatasi masalah

| Gejala | Penyebab & solusi |
|--------|-------------------|
| **Bad Gateway / 502 / "no available server"** | Domain belum memakai `:8080` (bagian 3a), atau container `app` belum healthy — cek Logs `app`. |
| Log `app`: `Database tidak bisa dihubungi` | Service `db` belum sehat. Cek Logs `db`. Jangan ubah `SERVICE_PASSWORD_POSTGRES` setelah deploy pertama. |
| Log `app`: `APP_KEY belum diisi` | Salin kunci yang tercetak di log ke variabel `APP_KEY` (bagian 3b). |
| Setelah pindah server, email/AI tidak jalan | `APP_KEY` berbeda dari server lama → isi ulang password SMTP & API key di Pengaturan. |
| Tombol Backup/Restore macet di "Berjalan" | Container `worker` mati — cek Logs `worker`, lalu Restart. |
| Backup gagal: `pg_dump: server version mismatch` | Versi PostgreSQL di `docker-compose.yml` (`postgres:16`) harus sama dengan `PG_MAJOR` di `Dockerfile` (16). |
| Gambar upload tidak tampil | Restart `app` (symlink storage dibuat ulang otomatis). |
| Link/aset memakai `http://` atau muncul peringatan "tidak aman" | Pastikan `APP_URL` diawali `https://`. |
| Restore gagal: `unrecognized configuration parameter "transaction_timeout"` | Backup berasal dari PostgreSQL 17. Samakan versi: ubah `postgres:16-alpine` → `postgres:17-alpine` **dan** `PG_MAJOR=16` → `17` di `Dockerfile` (hanya untuk instalasi baru — volume `db-data` versi lama tidak bisa dibuka versi lain). |
| Upload file besar gagal | Batas upload 200 MB (backup/update); foto maks 5 MB. |
| Build gagal `bookworm ... not found` | Ubah `ARG DEBIAN_SUITE=bookworm` di `Dockerfile` menjadi `trixie`. |

---

## 9. Referensi teknis

- `Dockerfile` — image PHP 8.4-FPM + nginx + client PostgreSQL 16.
  Aset frontend dibangun ulang saat build.
- `docker/entrypoint.sh` — urutan start per peran (`CONTAINER_ROLE`).
- `docker/nginx/site.conf` — nginx port 8080; memblokir file tersembunyi,
  PHP di folder upload, dan membatasi SVG lama dengan CSP.
- `docker/php/` — batas upload 200 MB, OPcache, pool php-fpm.
- Variabel bawaan di image (tidak perlu diisi): `APP_ENV=production`,
  `APP_DEBUG=false`, `DB_HOST=db`, `SESSION_SECURE_COOKIE=true`,
  `TRUSTED_PROXIES=*`, `APP_SELF_UPDATE=false`, `LOG_STACK=single,stderr`,
  cache/queue/session memakai database.
