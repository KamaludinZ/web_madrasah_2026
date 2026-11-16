<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use App\Models\PageView;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    public function test_settings_are_loaded_with_a_single_query_per_request(): void
    {
        Setting::set('site_name', 'MTsN Uji');
        Setting::flushCache();

        DB::enableQueryLog();
        Setting::get('site_name');
        Setting::get('site_description');
        Setting::get('belum_ada', 'bawaan');
        $settingQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], '"settings"'));
        DB::disableQueryLog();

        $this->assertCount(1, $settingQueries);
        $this->assertSame('MTsN Uji', Setting::get('site_name'));
        $this->assertSame('bawaan', Setting::get('belum_ada', 'bawaan'));
    }

    public function test_setting_changes_are_visible_immediately(): void
    {
        $this->assertSame('lama', Setting::get('uji_perubahan', 'lama'));

        Setting::set('uji_perubahan', 'baru');
        $this->assertSame('baru', Setting::get('uji_perubahan'));

        Setting::where('key', 'uji_perubahan')->first()->update(['value' => 'lewat model']);
        $this->assertSame('lewat model', Setting::get('uji_perubahan'));
    }

    public function test_category_news_count_uses_with_count_without_extra_queries(): void
    {
        $categories = Category::withCount(['news' => fn ($q) => $q->published()])->get();

        DB::enableQueryLog();
        $categories->each(fn ($category) => $category->news_count);
        $this->assertCount(0, DB::getQueryLog(), 'news_count tidak boleh menjalankan query per kategori.');
        DB::disableQueryLog();
    }

    public function test_bots_are_not_recorded_as_visitors(): void
    {
        $this->assertTrue(PageView::isBot('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'));
        $this->assertTrue(PageView::isBot('facebookexternalhit/1.1'));
        $this->assertTrue(PageView::isBot('WhatsApp/2.23'));
        $this->assertTrue(PageView::isBot('curl/8.5.0'));
        $this->assertTrue(PageView::isBot(''));
        $this->assertFalse(PageView::isBot('Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/130.0 Mobile Safari/537.36'));

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; bingbot/2.0)')->get('/berita')->assertOk();
        $this->assertSame(0, PageView::count());

        $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0) Chrome/130.0')->get('/berita')->assertOk();
        $this->assertSame(1, PageView::count());
    }

    public function test_bot_does_not_increase_news_view_count_or_touch_updated_at(): void
    {
        $news = News::create([
            'title' => 'Berita Uji', 'slug' => 'berita-uji', 'content' => '<p>x</p>',
            'category_id' => Category::first()->id, 'user_id' => $this->superAdmin()->id,
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $updatedAt = $news->fresh()->updated_at;
        $this->travel(5)->minutes();

        $this->withHeader('User-Agent', 'Googlebot/2.1')->get('/berita/berita-uji')->assertOk();
        $this->assertSame(0, (int) $news->fresh()->view_count);

        $this->withHeader('User-Agent', 'Mozilla/5.0 Chrome/130.0')->get('/berita/berita-uji')->assertOk();
        $this->assertSame(1, (int) $news->fresh()->view_count);
        $this->assertTrue($updatedAt->equalTo($news->fresh()->updated_at), 'Dibaca ≠ diubah.');
    }

    public function test_footer_visitor_counters_are_cached(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 Chrome/130.0')->get('/berita')->assertOk();
        $first = PageView::publicCounters();

        PageView::create(['page' => '/x', 'session_id' => 'lain', 'viewed_date' => today()]);

        $this->assertSame($first, PageView::publicCounters(), 'Angka footer di-cache 5 menit.');
    }

    public function test_search_routes_are_rate_limited(): void
    {
        $route = app('router')->getRoutes()->getByName('search.ajax');

        $this->assertContains('throttle:120,1', $route->gatherMiddleware());
        $this->assertContains('throttle:60,1', app('router')->getRoutes()->getByName('search.index')->gatherMiddleware());
    }

    public function test_quick_search_overlay_does_not_depend_on_htmx(): void
    {
        $html = $this->get('/berita')->assertOk()->getContent();

        $this->assertStringNotContainsString('hx-get', $html);
        $this->assertStringContainsString('data-search-url="' . route('search.ajax') . '"', $html);
        $this->assertMatchesRegularExpression('#<form action="' . preg_quote(route('search.index'), '#') . '" method="GET" role="search"#', $html);
    }
}
