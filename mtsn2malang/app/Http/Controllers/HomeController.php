<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Facility;
use App\Models\News;
use App\Models\PageView;
use App\Models\PortalApp;
use App\Models\Program;
use App\Models\Setting;
use App\Models\SocialMediaContent;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        PageView::record('/');

        $banners = Banner::where('is_active', true)->orderBy('order')->get();
        $defaultBannerImage       = Setting::get('default_banner_image');
        $defaultBannerSubtitle    = Setting::get('default_banner_subtitle', '');
        $defaultBannerTitle       = Setting::get('default_banner_title', 'MTsN 2 Kota Malang');
        $defaultBannerDescription = Setting::get('default_banner_description', 'Unggul, Berkarakter, dan Berwawasan Global');
        $defaultBannerLink        = Setting::get('default_banner_link', '');
        $latestNews = News::published()->with(['category', 'author'])->latest('published_at')->take(6)->get();
        $popularNews = News::popular()->with(['category', 'author'])->take(6)->get();
        $prestasiNews = News::published()->with(['category', 'author'])
            ->withAnyTags(['Prestasi', 'Prestasi Siswa', 'Prestasi Madrasah', "Prestasi Ma'had", 'Prestasi Santri'])
            ->latest('published_at')->take(6)->get();
        $pengumumanNews = News::published()->with(['category', 'author'])
            ->whereHas('category', fn ($q) => $q->where('slug', 'pengumuman'))
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

        $unggulanPrograms = Program::where('type', 'unggulan')->where('is_active', true)->orderBy('order')->take(6)->get();
        $socialMediaContents = SocialMediaContent::where('is_active', true)
            ->where('show_on_madrasah', true)
            ->orderBy('order')
            ->take(9)
            ->get();
        $sarpras = Facility::where('is_active', true)->orderBy('order')->take(12)->get();
        $testimonials = Testimonial::where('is_active', true)->orderBy('order')->take(9)->get();
        $showTestimonialsSection = (bool) Setting::get('show_testimonials_section', true);
        $portalApps = PortalApp::where('is_active', true)->orderBy('order')->get();

        return view('pages.home', compact(
            'banners', 'defaultBannerImage',
            'defaultBannerSubtitle', 'defaultBannerTitle', 'defaultBannerDescription', 'defaultBannerLink',
            'latestNews', 'popularNews', 'prestasiNews', 'pengumumanNews',
            'prestasiStats', 'prestasiList', 'unggulanPrograms', 'socialMediaContents',
            'sarpras', 'testimonials', 'showTestimonialsSection', 'portalApps'
        ));
    }
}
