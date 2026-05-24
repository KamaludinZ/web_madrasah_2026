<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PageContent;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $roles = [
            'super_admin' => 'Super Administrator',
            'editor_in_chief' => 'Editor in Chief',
            'editor' => 'Editor',
            'journalist' => 'Jurnalis/Penulis',
            'contributor' => 'Kontributor',
            'moderator' => 'Moderator',
            'staff_tu' => 'Staff Tata Usaha',
        ];

        foreach ($roles as $name => $label) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Create Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@mtsn2kotamalang.sch.id'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('Admin@MTsN2!2024'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super_admin');

        // Create Default Settings
        $settings = [
            'site_name' => 'MTsN 2 Kota Malang',
            'site_tagline' => 'Unggul, Berkarakter, dan Berwawasan Global',
            'announcement_text' => 'Selamat datang di website resmi MTsN 2 Kota Malang. Madrasah Tsanawiyah Negeri 2 Kota Malang - Unggul, Berkarakter, dan Berwawasan Global.',
            'academic_year' => '2024/2025',
            'ai_provider' => 'gemini',
            'whatsapp_number' => '085163350000',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // Create Default Categories
        $categories = [
            ['name' => 'Akademik', 'slug' => 'akademik', 'color' => '#1a7a3c'],
            ['name' => 'Ekstrakurikuler', 'slug' => 'ekstrakurikuler', 'color' => '#f97316'],
            ['name' => 'Prestasi', 'slug' => 'prestasi', 'color' => '#eab308'],
            ['name' => 'Info Sekolah', 'slug' => 'info-sekolah', 'color' => '#3b82f6'],
            ['name' => 'Kegiatan', 'slug' => 'kegiatan', 'color' => '#8b5cf6'],
            ['name' => 'Pengumuman', 'slug' => 'pengumuman', 'color' => '#ef4444'],
            ['name' => 'Ma\'had', 'slug' => 'mahad', 'color' => '#0891b2'],
            ['name' => 'PPDB', 'slug' => 'ppdb', 'color' => '#059669'],
        ];

        foreach ($categories as $index => $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], array_merge($cat, ['order' => $index + 1, 'is_active' => true]));
        }

        // Create Default Page Contents
        $pages = [
            [
                'page_key' => 'sambutan',
                'title' => 'Sambutan Kepala Madrasah',
                'content' => '<p>Assalamu\'alaikum Warahmatullahi Wabarakatuh</p>
<p>Puji syukur kita panjatkan kehadirat Allah SWT yang telah memberikan rahmat dan hidayah-Nya kepada kita semua. Shalawat dan salam semoga senantiasa tercurahkan kepada junjungan kita Nabi Muhammad SAW, keluarga, sahabat, dan seluruh umatnya hingga akhir zaman.</p>
<p>Selamat datang di website resmi MTsN 2 Kota Malang. Madrasah Tsanawiyah Negeri 2 Kota Malang merupakan lembaga pendidikan Islam yang berkomitmen untuk mencetak generasi yang unggul dalam ilmu pengetahuan, berkarakter mulia, dan berwawasan global.</p>
<p>Kami senantiasa berupaya meningkatkan kualitas pendidikan melalui berbagai program unggulan, pengembangan kurikulum yang inovatif, serta pembinaan karakter yang berlandaskan nilai-nilai Islam. Dengan dukungan tenaga pendidik yang profesional dan fasilitas yang memadai, kami berkomitmen untuk memberikan pelayanan pendidikan terbaik bagi putra-putri bangsa.</p>
<p>Semoga Allah SWT senantiasa meridhoi langkah-langkah kita dalam mencerdaskan kehidupan bangsa dan mewujudkan generasi yang berilmu, beriman, dan berakhlak mulia.</p>
<p>Wassalamu\'alaikum Warahmatullahi Wabarakatuh</p>',
            ],
            [
                'page_key' => 'selayang_pandang',
                'title' => 'Selayang Pandang MTsN 2 Kota Malang',
                'content' => '<p>Madrasah Tsanawiyah Negeri 2 Kota Malang (MTsN 2 Kota Malang) merupakan salah satu madrasah negeri yang berada di bawah naungan Kementerian Agama Republik Indonesia. Berlokasi di Jl. Raya Cemorokandang No. 77, Kota Malang, madrasah ini telah berdiri dan berkembang menjadi salah satu lembaga pendidikan Islam terkemuka di Kota Malang.</p>
<p>Dengan visi menjadi madrasah yang unggul, berkarakter, dan berwawasan global, MTsN 2 Kota Malang terus berinovasi dalam memberikan layanan pendidikan berkualitas. Madrasah ini memiliki berbagai program unggulan yang dirancang untuk mengembangkan potensi siswa secara holistik, meliputi aspek akademik, spiritual, dan sosial.</p>
<p>Didukung oleh tenaga pendidik yang berpengalaman dan berdedikasi, serta fasilitas pembelajaran yang lengkap dan modern, MTsN 2 Kota Malang berkomitmen untuk mencetak generasi penerus bangsa yang cerdas, berakhlak mulia, dan siap menghadapi tantangan global.</p>',
            ],
            [
                'page_key' => 'visi_misi',
                'title' => 'Visi, Misi dan Tujuan',
                'content' => '<h2>Visi</h2>
<p><strong>"Terwujudnya Madrasah yang Unggul dalam Prestasi, Berkarakter Islami, dan Berwawasan Global"</strong></p>
<h2>Misi</h2>
<ol>
<li>Menyelenggarakan pendidikan yang berkualitas dan berorientasi pada pengembangan potensi peserta didik secara optimal.</li>
<li>Menanamkan nilai-nilai keislaman dan akhlak mulia dalam setiap aspek kehidupan madrasah.</li>
<li>Mengembangkan program-program unggulan yang inovatif dan relevan dengan kebutuhan zaman.</li>
<li>Membangun kerjasama yang sinergis antara madrasah, orang tua, dan masyarakat.</li>
<li>Meningkatkan kompetensi tenaga pendidik dan kependidikan secara berkelanjutan.</li>
<li>Mewujudkan lingkungan madrasah yang kondusif, bersih, indah, dan ramah lingkungan.</li>
</ol>
<h2>Tujuan</h2>
<ol>
<li>Menghasilkan lulusan yang memiliki kompetensi akademik tinggi dan siap melanjutkan ke jenjang pendidikan yang lebih tinggi.</li>
<li>Membentuk peserta didik yang beriman, bertakwa, dan berakhlak mulia.</li>
<li>Mengembangkan bakat dan minat peserta didik melalui berbagai kegiatan ekstrakurikuler.</li>
<li>Mewujudkan madrasah sebagai pusat keunggulan pendidikan Islam di Kota Malang.</li>
</ol>',
            ],
        ];

        foreach ($pages as $page) {
            PageContent::firstOrCreate(['page_key' => $page['page_key']], $page);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin credentials:');
        $this->command->info('Email: admin@mtsn2kotamalang.sch.id');
        $this->command->info('Password: Admin@MTsN2!2024');
    }
}
