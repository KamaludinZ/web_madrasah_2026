# 🏫 Website MTsN 2 Kota Malang

**Platform CMS Modern untuk MTsN 2 Kota Malang**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-orange.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-blue.svg)](https://postgresql.org)

---

## 🌟 Fitur Utama

### 🎨 Frontend
- **Landing Page** modern dengan hero slider, berita terbaru, prestasi, sarpras, dan testimoni alumni
- **40+ Halaman** termasuk semua menu dan sub-menu navigasi
- **Dark/Light/System Mode** dengan toggle yang mudah
- **Aksesibilitas Lengkap** (WCAG 2.1 AA) - ukuran font, kontras tinggi, font disleksia, grayscale
- **Responsif** untuk semua perangkat (mobile, tablet, desktop)
- **Loading Screen** animasi yang elegan
- **Pencarian Dinamis** dengan AJAX real-time
- **Open Graph** untuk preview WhatsApp, Facebook, Telegram

### 🔐 Admin Panel (Filament PHP v3)
- **Dashboard** dengan statistik real-time (pengunjung, berita, komentar)
- **RBAC** - 7 role: Super Admin, Editor in Chief, Editor, Journalist, Contributor, Moderator, Staff TU
- **Login OTP** 6 digit untuk keamanan tambahan
- **AI Content Assistant** - Generate draf berita, SEO otomatis, perbaikan tata bahasa
- **Rich Text Editor** (TipTap) dengan drag & drop gambar
- **Media Library** dengan auto-WebP conversion

### 📰 Manajemen Konten
- Berita dengan kategori, tag, dan status (draf/review/publikasi/arsip)
- Banner/Slider yang dapat dikelola
- Daftar GTK (Guru & Tenaga Kependidikan)
- Ekstrakurikuler, Sarpras, Prestasi, Testimoni Alumni
- Portal Aplikasi
- Standar Pelayanan (PPID)
- Arsip PPID, SAKIP, Pengaduan, Survei
- PPDB & PSB Ma'had

### 🤖 AI Integration
- **Google Gemini Pro** / OpenAI GPT
- Generate draf berita dari poin kegiatan
- Auto-generate Meta Title, Meta Description, Slug
- Grammar Fixer untuk standar publikasi instansi

### 🔒 Keamanan
- Session-based authentication (tanpa Clerk)
- OTP 6 digit untuk 2FA
- View Counter dengan proteksi session/cookie
- CSRF Protection
- Security Headers (X-Frame-Options, CSP, dll)

### 📊 Migrasi WordPress
- Import berita, kategori, tag dari WordPress
- Preserve URL slug untuk SEO
- Import media/thumbnail

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 12.x |
| Admin Panel | Filament PHP v3 |
| Database | PostgreSQL |
| Frontend | Blade + Tailwind CSS v3 |
| Build Tool | Vite |
| Editor | TipTap (via Filament) |
| AI | Google Gemini Pro / OpenAI |
| Slider | Swiper.js |
| Animation | AOS (Animate on Scroll) |
| Lightbox | GLightbox |
| Auth | Laravel Session + OTP |
| Permissions | Spatie Laravel Permission |
| Tags | Spatie Laravel Tags |
| Slugs | Spatie Laravel Sluggable |

---

## 🚀 Instalasi Cepat

```bash
# Clone repository
git clone https://github.com/your-repo/mtsn2malang.git
cd mtsn2malang

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Storage link
php artisan storage:link

# Start server
php artisan serve
```

---

## 📁 Struktur Direktori

```
mtsn2malang/
├── app/
│   ├── Console/Commands/     # ImportWordPress command
│   ├── Filament/
│   │   ├── Pages/            # Dashboard, Settings, Maintenance
│   │   ├── Resources/        # 15+ Filament Resources
│   │   └── Widgets/          # Stats, Charts, Tables
│   ├── Http/Controllers/     # Web Controllers
│   ├── Models/               # 20+ Eloquent Models
│   ├── Providers/            # Service Providers
│   └── Services/             # AiService
├── database/
│   ├── migrations/           # 4 migration files
│   └── seeders/              # DatabaseSeeder
├── resources/
│   ├── css/app.css           # Tailwind CSS
│   ├── js/app.js             # Alpine.js + Swiper + AOS
│   └── views/
│       ├── layouts/          # app.blade.php, header, footer
│       ├── pages/            # 40+ page views
│       ├── components/       # Reusable components
│       ├── auth/             # Login with OTP
│       ├── errors/           # 404, 500, maintenance
│       └── filament/         # Custom Filament views
├── routes/web.php            # All routes
├── DEPLOYMENT.md             # Panduan deployment cPanel
└── README.md                 # Dokumentasi ini
```

---

## 👥 Role & Akses

| Role | Akses |
|------|-------|
| Super Admin | Full access semua fitur |
| Editor in Chief | Semua konten + approve berita |
| Editor | Kelola berita + approve dari journalist |
| Journalist | Tulis & edit berita sendiri |
| Contributor | Tulis berita (perlu approval) |
| Moderator | Moderasi komentar + pengaduan |
| Staff TU | Kelola data GTK, sarpras, dll |

---

## 🔑 Kredensial Default

| | |
|--|--|
| **URL Admin** | `/admin` |
| **Email** | `admin@mtsn2kotamalang.sch.id` |
| **Password** | `Admin@MTsN2!2024` |

> ⚠️ **GANTI PASSWORD** setelah login pertama!

---

## 📞 Kontak

- **Website:** https://mtsn2kotamalang.sch.id
- **Email:** mtsnmalang2adm@gmail.com
- **Telepon:** (0341) 711500
- **Alamat:** Jl. Raya Cemorokandang No. 77, Kota Malang 65138

---

## 📄 Lisensi

Hak Cipta © 2024 MTsN 2 Kota Malang. Semua hak dilindungi.
