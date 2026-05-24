<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Facility;
use App\Models\News;
use App\Models\PageView;
use App\Models\PortalApp;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        PageView::record('/');

        $banners = Banner::where('is_active', true)->orderBy('order')->get();
        $latestNews = News::published()->with(['category', 'author'])->latest('published_at')->take(6)->get();
        $popularNews = News::popular()->with(['category', 'author'])->take(6)->get();
        $prestasiNews = News::published()->with(['category', 'author'])
            ->whereHas('category', fn($q) => $q->where('slug', 'prestasi'))
            ->latest('published_at')->take(6)->get();

        $academicYear = Setting::get('academic_year', '2024/2025');
        $prestasiStats = [
            'total' => Achievement::where('academic_year', $academicYear)->count(),
            'regional' => Achievement::where('academic_year', $academicYear)->where('level', 'regional')->count(),
            'provinsi' => Achievement::where('academic_year', $academicYear)->where('level', 'provinsi')->count(),
            'nasional' => Achievement::where('academic_year', $academicYear)->where('level', 'nasional')->count(),
            'internasional' => Achievement::where('academic_year', $academicYear)->where('level', 'internasional')->count(),
        ];

        $prestasiList = Achievement::where('academic_year', $academicYear)
            ->latest('achievement_date')
            ->paginate(10);

        $sarpras = Facility::where('is_active', true)->orderBy('order')->take(12)->get();
        $testimonials = Testimonial::where('is_active', true)->orderBy('order')->take(9)->get();
        $portalApps = PortalApp::where('is_active', true)->orderBy('order')->get();

        return view('pages.home', compact(
            'banners', 'latestNews', 'popularNews', 'prestasiNews',
            'prestasiStats', 'prestasiList', 'sarpras', 'testimonials', 'portalApps'
        ));
    }
}
