# Panduan Deploy ke Coolify — MTsN 2 Kota Malang

Panduan menjalankan website (Laravel 12 + Filament 3 + PostgreSQL) di server
sendiri memakai [Coolify](https://coolify.io) v4, langsung dari **repository
GitHub publik**. Semua file yang dibutuhkan sudah ada di repo:
`Dockerfile`, `docker-compose.yml`, dan folder `docker/`.

Ada **dua jalur** — pilih salah satu:

| | **Jalur A — Dockerfile** *(disarankan)* | **Jalur B — Docker Compose** |
|---|---|---|
| Database | Resource PostgreSQL **terpisah** di Coolify (dibuat sendiri, bisa di-backup terjadwal oleh Coolify) | Ikut di dalam compose (container `db`) |
| Container aplikasi | **1 container**: web + panel admin + antrian + jadwal | 3 container: `app`, `worker`, `scheduler` |
| Cocok untuk | Server kecil–menengah, pengelolaan database terpisah | Semua dalam satu paket, satu kali klik |

> Deploy lama lewat cPanel/FTP tetap bisa dipakai (lihat `DEPLOYMENT.md`).

---

## Daftar isi

1. [Gambaran cara kerja](#1-gambaran-cara-kerja)
2. [Persiapan server, domain, dan repository](#2-persiapan)
3. [Jalur A — Dockerfile + PostgreSQL terpisah](#3-jalur-a--dockerfile--postgresql-terpisah)
4. [Jalur B — Docker Compose](#4-jalur-b--docker-compose)
5. [Setelah deploy pertama](#5-setelah-deploy-pertama)
6. [Cek kesehatan](#6-cek-kesehatan)
7. [Memindahkan data dari hosting lama](#7-memindahkan-data-dari-hosting-lama)
8. [Operasional sehari-hari](#8-operasional-sehari-hari)
9. [Mengatasi masalah](#9-mengatasi-masalah)
10. [Referensi variabel & teknis](#10-referensi)

---

## 1. Gambaran cara kerja

Setiap kali container aplikasi menyala, **otomatis** terjadi:

1. Koneksi database dibaca (dari `DB_URL` atau `DB_HOST`/`DB_*`).
2. Folder penyimpanan disiapkan beserta hak aksesnya.
3. Menunggu database siap (maks. 120 detik).
4. Memilih client PostgreSQL (`pg_dump`/`psql`) yang **versinya sama dengan
   server** — image memuat versi 15, 16, 17, dan 18 — agar fitur
   **Backup & Restore** selalu berfungsi.
5. Migrasi database (`migrate --force`, aman bila beberapa container start bersamaan).
6. Bila database masih kosong (deploy pertama): membuat akun super admin
   dan data awal.
7. Menyusun cache, lalu menyalakan nginx + PHP (+ antrian & jadwal pada Jalur A).

**Data permanen** (tidak hilang saat redeploy) hanya ada di dua tempat:

| Tempat | Isi |
|---|---|
| Volume **storage** (`/var/www/html/storage`) | Foto/berkas upload, file backup, log, sesi, kunci `APP_KEY` cadangan |
| Database PostgreSQL | Seluruh isi website |

> ⚠️ Pada **Jalur A**, volume storage **wajib ditambahkan manual** (langkah 3.3).
> Tanpa itu semua foto upload hilang setiap redeploy — log container akan
> memperingatkan dengan jelas.

---

## 2. Persiapan

### 2.1 Server
- Coolify v4 sudah terpasang. Disarankan **RAM ≥ 2 GB** (build aset frontend
  butuh memori) dan disk kosong ≥ 10 GB.

### 2.2 Domain
- Buat record DNS **A** → IP server Coolify untuk domain Anda
  (mis. `mtsn2kotamalang.sch.id`, dan `www` bila perlu). Tunggu sampai aktif
  (cek: `ping mtsn2kotamalang.sch.id` mengarah ke IP server).
- Belum punya domain? Bisa sementara memakai domain otomatis Coolify
  (`http://xxxx.sslip.io`). Login admin tetap berfungsi di `http://` karena
  cookie aman (`SESSION_SECURE_COOKIE`) otomatis mengikuti `APP_URL`.

### 2.3 Repository GitHub publik
- Alamat repo: `https://github.com/KamaludinZ/web_madrasah_2026`, branch **`main`**.
- Kode aplikasi berada di **subfolder `mtsn2malang/`** — karena itu setiap
  resource memakai **Base Directory = `/mtsn2malang`**.
- Repo **publik** tidak butuh GitHub App/kunci deploy. (Bila suatu saat repo
  dijadikan privat: Coolify → **Sources → + Add → GitHub App**, lalu pakai
  pilihan *Private Repository (with GitHub App)*.)

### 2.4 Siapkan nilai rahasia (catat di tempat aman)

| Nilai | Cara membuat |
|---|---|
| **`APP_KEY`** | Pindah dari hosting lama → **pakai nilai yang sama** dari `.env` lama. Instalasi baru → buat di komputer yang punya PHP: `php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"`, atau kosongkan dulu (container membuatnya sendiri dan mencetaknya di log). |
| **`ADMIN_DEFAULT_PASSWORD`** | Password super admin awal (min. 12 karakter campuran). Kosong = dibuat acak & dicetak di log deploy pertama. |
| Password database | Dibuat Coolify otomatis (Jalur A: di resource PostgreSQL; Jalur B: `SERVICE_PASSWORD_POSTGRES`). |

> ⚠️ `APP_KEY` **tidak boleh berubah** setelah website dipakai — kunci ini
> mengenkripsi sesi login, password SMTP, dan API key AI di Pengaturan.

---

## 3. Jalur A — Dockerfile + PostgreSQL terpisah

### 3.1 Buat database PostgreSQL

1. Coolify → **Projects** → pilih/buat project (mis. `MTsN 2`) → environment
   **production** → **+ New** → **Databases** → **PostgreSQL**.
2. Pilih **versi 16 atau 17** (keduanya didukung; image aplikasi menyesuaikan
   otomatis). Pilih server/destination **yang sama** dengan aplikasi nanti.
3. Di halaman database, isi/periksa:
   - **Name**: `mtsn2-db`
   - **Initial Database**: `mtsn2malang`
   - **Username** & **Password**: biarkan yang dibuat Coolify (atau ganti).
   - **Make it publicly available**: **jangan dicentang** (cukup jaringan
     internal Coolify — lebih aman).
4. Klik **Start**, tunggu status **Running (healthy)**.
5. Salin nilai **Postgres URL (internal)**, bentuknya:
   ```
   postgres://USERNAME:PASSWORD@NAMA-CONTAINER:5432/mtsn2malang
   ```
   Nilai ini dipakai sebagai `DB_URL` di langkah 3.4.
6. (Sangat disarankan) Tab **Backups** → aktifkan backup terjadwal harian,
   simpan ke S3/penyimpanan eksternal bila ada.

> Password berisi karakter khusus (`@`, `/`, `:`, `#`)? Lebih aman pakai
> variabel terpisah `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
> `DB_PASSWORD` (lihat bagian 10) daripada `DB_URL`.

### 3.2 Buat aplikasi dari repo publik

1. Di project & environment yang **sama** → **+ New** → **Public Repository**.
2. **Repository URL**: `https://github.com/KamaludinZ/web_madrasah_2026` → **Check repository**.
3. Isi:
   - **Branch**: `main`
   - **Build Pack**: **Dockerfile**
   - **Base Directory**: `/mtsn2malang`
   - **Dockerfile Location**: `/Dockerfile`
   - **Ports Exposes**: `8080`
4. **Continue**.

### 3.3 Volume penyimpanan (WAJIB)

Tab **Persistent Storage** → **+ Add** → **Volume**:

| Kolom | Isi |
|---|---|
| Name | `mtsn2-storage` |
| Destination Path | `/var/www/html/storage` |

Simpan.

### 3.4 Environment Variables

Tab **Environment Variables** (mode *Developer view* boleh, tempel sekaligus):

```env
APP_URL=https://mtsn2kotamalang.sch.id
APP_KEY=base64:ISI_KUNCI_ANDA
DB_URL=postgres://USERNAME:PASSWORD@NAMA-CONTAINER:5432/mtsn2malang
ADMIN_DEFAULT_PASSWORD=GantiDenganPasswordKuat123!
```

Catatan:
- `APP_URL` tanpa `:8080` dan tanpa garis miring di akhir.
- **Jangan** centang *Build Variable* pada variabel-variabel ini — semuanya
  dibaca saat container **berjalan**, bukan saat build (rahasia tidak boleh
  ikut tertanam di image).
- Variabel lain sudah punya nilai bawaan yang aman (bagian 10).

### 3.5 Domain & health check

- Tab **General** → **Domains**: `https://mtsn2kotamalang.sch.id`
  (tambahkan `,https://www.mtsn2kotamalang.sch.id` bila perlu). Coolify
  membuat sertifikat HTTPS otomatis.
- Tab **Health Checks** (opsional, disarankan): aktifkan, **Path** `/up`,
  **Port** `8080`. (Image juga sudah punya `HEALTHCHECK` sendiri.)

### 3.6 Deploy

Klik **Deploy**. Build pertama ±5–10 menit. Lanjut ke [bagian 5](#5-setelah-deploy-pertama).

---

## 4. Jalur B — Docker Compose

1. Project/environment → **+ New** → **Public Repository** →
   URL `https://github.com/KamaludinZ/web_madrasah_2026` → **Check repository**.
2. Isi:
   - **Branch**: `main`
   - **Build Pack**: **Docker Compose**
   - **Base Directory**: `/mtsn2malang`
   - **Docker Compose Location**: `/docker-compose.yml`
3. **Continue**. Coolify menampilkan 4 service: `app`, `worker`, `scheduler`, `db`.
4. **Domain** — pada service **`app`**, isi **Domains** dengan domain
   **ditambah `:8080`** (port di dalam container, bukan port publik):
   ```
   https://mtsn2kotamalang.sch.id:8080
   ```
   Service `worker`, `scheduler`, `db` **tidak** diberi domain.
5. **Environment Variables**:
   ```env
   APP_URL=https://mtsn2kotamalang.sch.id
   APP_KEY=base64:ISI_KUNCI_ANDA
   ADMIN_DEFAULT_PASSWORD=GantiDenganPasswordKuat123!
   ```
   `SERVICE_USER_POSTGRES` & `SERVICE_PASSWORD_POSTGRES` dibuat otomatis
   oleh Coolify — **jangan diubah** setelah deploy pertama (database sudah
   memakai password itu). Volume `app-storage` & `db-data` dibuat otomatis.
6. Klik **Deploy**.

> PostgreSQL di compose memakai versi **16**. Jangan menaikkan versi mayor
> pada database yang sudah berisi data (butuh dump & restore — bagian 7B).

---

## 5. Setelah deploy pertama

1. Buka **Logs** container aplikasi (`app`). Deploy pertama yang sukses:
   ```
   [entrypoint:app] Koneksi database diambil dari DB_URL (host: ..., database: mtsn2malang).
   [entrypoint:app] Menunggu PostgreSQL di ...:5432 (maks 120 detik)...
   [entrypoint:app] Database siap.
   [entrypoint:app] Server PostgreSQL versi 17 — memakai pg_dump/psql versi 17.
   [entrypoint:app] Queue worker di container ini: true, scheduler: true.
   [entrypoint:app] Menjalankan migrasi database...
   [entrypoint:app] Database baru (belum ada pengguna) — menjalankan seeder awal...
   Admin credentials:
   Email: admin@mtsn2kotamalang.sch.id
   Password: (password Anda / yang dibuat acak)
   [entrypoint:app] Siap. Menjalankan: supervisord ...
   ```
   (Pada Jalur B baris *Queue worker* bernilai `false` — worker punya container sendiri.)
2. Bila `APP_KEY` dikosongkan: salin baris `APP_KEY=base64:...` dari log ke
   Environment Variables, simpan, lalu **Redeploy**.
3. Tunggu status **Running (healthy)**, buka `https://domain` dan
   `https://domain/admin/login`, login sebagai `admin@mtsn2kotamalang.sch.id`.
4. Di panel admin:
   1. **Ganti password** super admin (menu profil kanan atas).
   2. **Pengaturan Website → Umum**: logo, favicon, nama situs.
   3. **Pengaturan Website → Email**: isi SMTP → **Kirim Email Tes** sampai berhasil.
   4. Baru setelah email berhasil, aktifkan **OTP** di tab **Keamanan** (opsional).
   5. **Konten → Sambutan & Profil**: isi profil madrasah (tampil di Tab Tentang Portal).
   6. **Portal**: isi **Akses Cepat**, **Kategori Portal** (+ link anak), dan
      **Pengaturan Portal** (gambar latar, saklar Portal).
5. Tab **Update Aplikasi** di panel **sengaja dinonaktifkan** di Docker —
   update dilakukan lewat GitHub (bagian 8).

---

## 6. Cek kesehatan

| Yang dicek | Cara | Hasil yang benar |
|---|---|---|
| Aplikasi hidup | Buka `https://domain/up` | Halaman "Application up" |
| Halaman Portal | Buka `https://domain/` | Portal (kop, tab Portal & Tentang); Beranda di `/beranda` |
| Antrian berjalan | Panel → Pengaturan Website → **Backup & Restore** → **Buat Backup Sekarang** | Status *Selesai* dalam ±1 menit |
| Backup cocok dengan versi DB | Unduh backup tadi → ukuran > 0 dan tidak ada error di daftar | — |
| Upload gambar | Tambah berita bergambar → tampil di halaman berita | Gambar tampil |
| Data permanen | Klik **Redeploy**, lalu cek gambar & login tetap ada | Tidak hilang |
| Email | Pengaturan Website → Email → **Kirim Email Tes** | Email diterima |

---

## 7. Memindahkan data dari hosting lama

### 7A — Lewat fitur Backup & Restore (paling mudah, file ≤ 190 MB)

1. Pastikan `APP_KEY` di Coolify **sama** dengan `.env` server lama **sebelum** deploy pertama.
2. Panel **server lama** → Pengaturan Website → **Backup & Restore** →
   **Buat Backup Sekarang** → **Unduh** ZIP-nya.
3. Deploy di Coolify (bagian 3 atau 4), login dengan password dari log.
4. Panel **server baru** → **Backup & Restore** → pilih ZIP → ketik `RESTORE` → jalankan.
5. **Restart** container aplikasi agar migrasi terbaru dijalankan di atas
   data lama. Login kini memakai akun & password dari server lama.

> Backup dari PostgreSQL versi lebih baru tidak bisa direstore ke server
> yang lebih lama (mis. backup PG 17 → server PG 16). Pakai versi database
> yang sama atau lebih baru di Coolify.

### 7B — Manual (data besar / fitur backup tidak jalan)

1. Di server lama:
   ```bash
   pg_dump --no-owner --no-privileges -h localhost -U USER_DB NAMA_DB > data.sql
   tar czf uploads.tar.gz -C storage/app public
   ```
2. Salin kedua file ke server Coolify (mis. `scp`), lalu via SSH:
   ```bash
   docker ps --format '{{.Names}}'          # cari nama container DB & aplikasi

   # Database: kosongkan lalu impor
   docker exec -i CONTAINER_DB psql -U USER -d mtsn2malang -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"
   docker exec -i CONTAINER_DB psql -U USER -d mtsn2malang < data.sql

   # File upload
   docker cp uploads.tar.gz CONTAINER_APP:/tmp/
   docker exec CONTAINER_APP sh -c "tar xzf /tmp/uploads.tar.gz -C /var/www/html/storage/app && chown -R www-data:www-data /var/www/html/storage/app/public"
   ```
   `USER` = username database (Jalur A: di halaman resource PostgreSQL;
   Jalur B: `SERVICE_USER_POSTGRES`).
3. **Restart** container aplikasi.

---

## 8. Operasional sehari-hari

### Update aplikasi
1. Perubahan di-push/merge ke branch **`main`** di GitHub.
2. Di Coolify klik **Redeploy**. Untuk repo publik, deploy otomatis bisa
   dipasang lewat **Webhooks**: salin URL webhook *Deploy* dari Coolify ke
   GitHub → *Settings → Webhooks* (event `push`).
3. Migrasi berjalan otomatis; volume & database tidak tersentuh.
4. Bila build gagal, versi lama **tetap berjalan** — perbaiki lalu deploy ulang.

### Pembaruan keamanan PHP
Versi PHP di image **dikunci** lewat `ARG PHP_VERSION` di `Dockerfile`
(saat ini `8.4.27`, sudah menambal PHP2Shell CVE-2026-109618 &
CVE-2026-109619). Saat ada rilis keamanan PHP baru: naikkan angka itu,
push ke `main`, lalu **Redeploy**. Build otomatis **gagal** bila versi PHP
masih rentan (daftar versi aman di `app/Support/PhpSecurity.php`), dan
dashboard admin menampilkan peringatan merah untuk super admin.

Cek versi yang sedang berjalan: Terminal container `app` → `php -v`.

### Menjalankan perintah artisan
Coolify → container aplikasi (`app`) → **Terminal**, gunakan **`app-artisan`**
(bukan `php artisan` — terminal masuk sebagai root dan bisa membuat file
yang tidak bisa ditulis website):
```bash
app-artisan migrate:status
app-artisan optimize:clear
app-artisan queue:failed            # job antrian yang gagal
# Contoh: matikan OTP bila email bermasalah dan admin tidak bisa login
app-artisan tinker --execute="App\Models\Setting::set('login_otp_enabled', false)"
# Contoh: nyalakan lagi Halaman Portal dari terminal
app-artisan tinker --execute="App\Support\PortalSettings::setEnabled(true)"
```

### Log
- Coolify → container → **Logs** (paling lengkap; termasuk antrian & jadwal pada Jalur A).
- Panel admin → Pengaturan Website → tab **Monitoring Log**.

### Backup
- **Database**: Jalur A → backup terjadwal di resource PostgreSQL Coolify.
- **Database + file upload**: panel → Pengaturan Website → **Backup & Restore**
  → **Buat Backup Sekarang** → **Unduh**, simpan di luar server secara berkala.

### Mode maintenance
Panel admin → **Mode Maintenance**. Panel admin tetap bisa dibuka selama maintenance.

---

## 9. Mengatasi masalah

| Gejala | Penyebab & solusi |
|---|---|
| **Bad Gateway / 502 / "no available server"** | Jalur A: **Ports Exposes** harus `8080`. Jalur B: domain wajib diakhiri `:8080`. Atau container belum *healthy* — cek Logs. |
| Log: `Database tidak bisa dihubungi setelah 120 detik` | `DB_URL`/`DB_HOST` salah, atau aplikasi & database beda server/destination di Coolify. Pakai **Postgres URL (internal)**, bukan public. |
| Log: `DB_URL tidak valid` | Format harus `postgres://user:password@host:5432/nama_db`. Password berkarakter khusus → pakai variabel `DB_*` terpisah. |
| Log: `Folder /var/www/html/storage BUKAN volume persisten` | Jalur A: tambahkan Persistent Storage (langkah 3.3), lalu Redeploy. |
| Log: `APP_KEY belum diisi` | Salin kunci yang tercetak di log ke variabel `APP_KEY`, Redeploy. |
| Login admin langsung kembali ke halaman login | `APP_URL` tidak sama dengan alamat yang dibuka (mis. `https` vs `http`, atau `www` vs tanpa `www`). Samakan, Redeploy. |
| Tombol Backup/Restore macet di "Berjalan" | Antrian mati. Jalur A: cek Logs (baris `queue-worker`), pastikan `RUN_QUEUE_WORKER` tidak diisi `false`. Jalur B: cek Logs `worker`, Restart. |
| Log: `Server PostgreSQL versi X tidak punya client yang sama` | Versi database di luar 15–18. Pakai PostgreSQL 16/17. |
| Setelah pindah server, email/AI tidak jalan | `APP_KEY` berbeda dari server lama → isi ulang password SMTP & API key di Pengaturan. |
| Gambar upload tidak tampil | Pastikan volume storage terpasang; Restart (symlink dibuat ulang otomatis). |
| Link memakai `http://` / peringatan "tidak aman" | `APP_URL` harus diawali `https://`. |
| Upload file besar gagal | Batas upload 200 MB (backup); foto maks 5 MB. |
| Build gagal kehabisan memori | Server < 2 GB RAM — tambah swap/RAM, lalu deploy ulang. |
| Build gagal `bookworm ... not found` | Ubah `ARG DEBIAN_SUITE=bookworm` di `Dockerfile` menjadi `trixie`. |
| Container restart terus, log `exec format error` / `bad interpreter` | Skrip berakhir CRLF. Repo sudah memaksa LF lewat `.gitattributes`; pastikan tidak diubah editor Windows. |

---

## 10. Referensi

### Variabel yang bisa diisi

| Variabel | Wajib | Keterangan |
|---|---|---|
| `APP_URL` | **Ya** | Alamat situs, mis. `https://mtsn2kotamalang.sch.id` |
| `APP_KEY` | Sangat disarankan | `base64:...`; jangan berubah setelah dipakai |
| `DB_URL` | Jalur A | Postgres URL (internal) dari Coolify |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Alternatif `DB_URL` | Koneksi database terpisah (Jalur B diisi otomatis) |
| `ADMIN_DEFAULT_PASSWORD` | Tidak | Password super admin saat database masih kosong |
| `RUN_QUEUE_WORKER` | Tidak | Default `true` (Jalur A). `false` bila worker dijalankan terpisah |
| `RUN_SCHEDULER` | Tidak | Default `true` (Jalur A). `false` bila scheduler dijalankan terpisah |
| `SESSION_SECURE_COOKIE` | Tidak | Otomatis `true` bila `APP_URL` https, selain itu `false` |
| `AUTO_MIGRATE` / `AUTO_SEED` | Tidak | Default `true`. `false` untuk melewati migrasi/seeder saat start |
| `DB_WAIT_TIMEOUT` | Tidak | Detik menunggu database, default `120` |
| `MAINTENANCE_SECRET` | Tidak | Kata rahasia URL bypass mode maintenance |
| `LOG_LEVEL` | Tidak | Default `info`; `debug` hanya saat mencari masalah |

Nilai bawaan image (tidak perlu diisi): `APP_ENV=production`, `APP_DEBUG=false`,
`DB_CONNECTION=pgsql`, `DB_HOST=db`, `TRUSTED_PROXIES=*`, `APP_SELF_UPDATE=false`,
`LOG_STACK=single,stderr`, cache/queue/session memakai database.

### Berkas teknis
- `Dockerfile` — image PHP 8.4.27-FPM (versi dikunci, build ditolak bila PHP rentan) + nginx + client PostgreSQL 15–18; aset
  frontend dibangun ulang saat build.
- `docker/entrypoint.sh` — urutan start per peran (`CONTAINER_ROLE` = `app`,
  `worker`, `scheduler`), pembacaan `DB_URL`, pemilihan client PostgreSQL.
- `docker/supervisord.conf` — php-fpm, nginx, serta queue worker & scheduler
  (aktif sesuai `RUN_QUEUE_WORKER`/`RUN_SCHEDULER`).
- `docker/nginx/site.conf` — nginx port 8080; memblokir file tersembunyi,
  PHP di folder upload, dan membatasi SVG di folder upload.
- `docker/php/` — batas upload 200 MB, OPcache, pool php-fpm.
- `.gitattributes` — memaksa akhir baris LF untuk skrip & konfigurasi container.
