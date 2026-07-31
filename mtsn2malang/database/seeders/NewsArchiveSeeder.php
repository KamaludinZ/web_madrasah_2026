<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsArchiveSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->first();

        if (!$author) {
            $this->command?->warn('Tidak ada user ditemukan. Jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        $category = Category::query()->first();

        if (!$category) {
            $this->command?->warn('Tidak ada kategori ditemukan. Jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        $seedPlan = [
            ['year' => 2022, 'months' => [1, 3, 7, 11]],
            ['year' => 2023, 'months' => [2, 4, 8, 12]],
            ['year' => 2024, 'months' => [1, 5, 9, 10]],
            ['year' => 2025, 'months' => [2, 6]],
        ];

        $created = 0;

        foreach ($seedPlan as $plan) {
            $year = $plan['year'];

            foreach ($plan['months'] as $month) {
                $itemsPerMonth = 2;

                for ($i = 1; $i <= $itemsPerMonth; $i++) {
                    $title = sprintf(
                        'Arsip Berita %d-%02d #%d',
                        $year,
                        $month,
                        $i
                    );

                    $publishedAt = Carbon::create($year, $month, min(25, 3 + $i), 9 + $i, 0, 0);

                    News::query()->updateOrCreate(
                        ['slug' => Str::slug($title)],
                        [
                            'user_id' => $author->id,
                            'category_id' => $category->id,
                            'title' => $title,
                            'excerpt' => "Contoh berita arsip bulan {$month} tahun {$year} untuk pengujian filter.",
                            'content' => "<p>Ini adalah konten dummy untuk pengujian arsip berita {$year}-{$month}.</p>",
                            'status' => 'published',
                            'published_at' => $publishedAt,
                            'view_count' => random_int(10, 250),
                            'is_featured' => false,
                            'allow_comments' => true,
                            'source' => 'seed',
                        ]
                    );

                    $created++;
                }
            }
        }

        $this->command?->info("NewsArchiveSeeder selesai. Data arsip dibuat/diperbarui: {$created} berita.");
    }
}
