# 🚀 Panduan Deployment MTsN 2 Kota Malang Website
## ke cPanel Web Hosting DomaiNesia 3.0

---

## 📋 PRASYARAT

- PHP 8.2+ (aktifkan di cPanel → PHP Selector)
- PostgreSQL atau MySQL (DomaiNesia mendukung keduanya)
- Composer (tersedia via SSH)
- Node.js 18+ (untuk build assets)
- SSL Certificate (aktifkan Let's Encrypt di cPanel)

---

## 🗄️ LANGKAH 1: PERSIAPAN DATABASE

### Menggunakan PostgreSQL (Direkomendasikan):
1. Login ke cPanel DomaiNesia
2. Buka **PostgreSQL Databases**
3. Buat database baru: `mtsn2malang_db`
4. Buat user baru: `mtsn2malang_user`
5. Set password yang kuat
6. Assign user ke database dengan **All Privileges**

### Menggunakan MySQL (Alternatif):
1. Buka **MySQL Databases** di cPanel
2. Buat database: `mtsn2malang_db`
3. Buat user dan assign ke database
4. Ubah `DB_CONNECTION=mysql` di `.env`

---

## 📁 LANGKAH 2: UPLOAD FILE

### Metode A: Via Git (Direkomendasikan)
```bash
# SSH ke server
ssh username@mtsn2kotamalang.sch.id

# Clone repository
cd /home/username
git clone https://github.com/your-repo/mtsn2malang.git public_html_temp

# Atau upload via File Manager cPanel
```

### Metode B: Via File Manager cPanel
1. Buka **File Manager** di cPanel
2. Navigate ke `/home/username/`
3. Upload file ZIP project
4. Extract ke folder `mtsn2malang`

---

## ⚙️ LANGKAH 3: KONFIGURASI DOCUMENT ROOT

1. Di cPanel, buka **Domains** atau **Addon Domains**
2. Set Document Root ke: `/home/username/mtsn2malang/public`
3. Atau buat symlink:
```bash
ln -s /home/username/mtsn2malang/public /home/username/public_html
```

---

## 🔧 LANGKAH 4: KONFIGURASI .ENV

```bash
# SSH ke server
cd /home/username/mtsn2malang

# Copy .env.example
cp .env.example .env

# Edit .env
nano .env
```

Isi konfigurasi berikut:
```env
APP_NAME="MTsN 2 Kota Malang"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mtsn2kotamalang.sch.id

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=username_mtsn2malang_db
DB_USERNAME=username_mtsn2malang_user
DB_PASSWORD=your_strong_password

MAIL_MAILER=smtp
MAIL_HOST=mail.mtsn2kotamalang.sch.id
MAIL_PORT=587
MAIL_USERNAME=mtsnmalang2adm@gmail.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mtsnmalang2adm@gmail.com

GEMINI_API_KEY=your_gemini_api_key
```

---

## 📦 LANGKAH 5: INSTALL DEPENDENCIES

```bash
# SSH ke server
cd /home/username/mtsn2malang

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Install Node.js dependencies (di lokal, lalu upload hasil build)
npm install
npm run build

# Upload folder public/build ke server
```

> **Catatan Shared Hosting:** Build assets di lokal, lalu upload folder `public/build` ke server.

---

## 🗃️ LANGKAH 6: SETUP DATABASE

```bash
# Jalankan migrasi
php artisan migrate --force

# Jalankan seeder (data awal)
php artisan db:seed --force

# Buat storage link
php artisan storage:link
```

---

## 🔐 LANGKAH 7: KONFIGURASI PERMISSIONS

```bash
# Set permissions
chmod -R 755 /home/username/mtsn2malang
chmod -R 775 /home/username/mtsn2malang/storage
chmod -R 775 /home/username/mtsn2malang/bootstrap/cache

# Pastikan web server bisa menulis ke storage
chown -R username:nobody /home/username/mtsn2malang/storage
```

---

## 🌐 LANGKAH 8: KONFIGURASI .HTACCESS

File `.htaccess` sudah ada di folder `public/`. Pastikan isinya:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## 🔒 LANGKAH 9: SSL & KEAMANAN

1. Di cPanel, buka **SSL/TLS** → **Let's Encrypt SSL**
2. Install SSL untuk domain utama
3. Aktifkan **Force HTTPS Redirect**

### Tambahkan di .htaccess (sebelum RewriteEngine):
```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## ⚡ LANGKAH 10: OPTIMASI PRODUCTION

```bash
# Cache konfigurasi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## 📧 LANGKAH 11: KONFIGURASI EMAIL (SMTP)

1. Di cPanel, buka **Email Accounts**
2. Buat email: `admin@mtsn2kotamalang.sch.id`
3. Atau gunakan Gmail SMTP dengan App Password

### Gmail SMTP:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mtsnmalang2adm@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

---

## 🤖 LANGKAH 12: KONFIGURASI AI (GEMINI)

1. Buka [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Buat API Key baru
3. Tambahkan ke `.env`:
```env
GEMINI_API_KEY=AIzaSy...
AI_PROVIDER=gemini
```

---

## 🔄 LANGKAH 13: SETUP CRON JOB

Di cPanel, buka **Cron Jobs** dan tambahkan:
```
* * * * * /usr/local/bin/php /home/username/mtsn2malang/artisan schedule:run >> /dev/null 2>&1
```

---

## 👤 LANGKAH 14: LOGIN PERTAMA

Setelah deployment berhasil:

1. Buka: `https://mtsn2kotamalang.sch.id/admin`
2. Login dengan:
   - **Email:** `admin@mtsn2kotamalang.sch.id`
   - **Password:** `Admin@MTsN2!2024`
3. **SEGERA GANTI PASSWORD** setelah login pertama!

---

## 🔑 AKUN DEFAULT

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@mtsn2kotamalang.sch.id | Admin@MTsN2!2024 |

---

## 📱 LANGKAH 15: KONFIGURASI AWAL WEBSITE

Setelah login ke admin panel:

1. **Pengaturan Website** → Isi nama, tagline, pengumuman
2. **Banner/Slider** → Upload banner utama
3. **Konten Halaman** → Isi sambutan, selayang pandang, visi misi
4. **Daftar GTK** → Tambahkan data guru dan staff
5. **Kategori** → Sesuaikan kategori berita
6. **Pengguna** → Tambahkan editor dan penulis

---

## 🆘 TROUBLESHOOTING

### Error 500:
```bash
# Cek log error
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Error Database:
```bash
# Test koneksi
php artisan tinker
>>> DB::connection()->getPdo();
```

### Error Permission:
```bash
chmod -R 775 storage bootstrap/cache
```

### Error Storage Link:
```bash
php artisan storage:link --force
```

---

## 📞 SUPPORT

- **Email:** mtsnmalang2adm@gmail.com
- **Telepon:** (0341) 711500
- **DomaiNesia Support:** support@domainesia.com

---

## 🔄 UPDATE WEBSITE

```bash
# SSH ke server
cd /home/username/mtsn2malang

# Pull update terbaru
git pull origin main

# Install dependencies baru (jika ada)
composer install --no-dev --optimize-autoloader

# Jalankan migrasi baru
php artisan migrate --force

# Clear dan rebuild cache
php artisan optimize:clear
php artisan optimize
```

---

*Dokumentasi ini dibuat untuk MTsN 2 Kota Malang - Website CMS Laravel 12 + Filament PHP v3*
