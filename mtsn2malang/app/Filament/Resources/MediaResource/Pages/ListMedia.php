<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Models\GalleryPhoto;
use App\Models\GalleryPost;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ListMedia extends Page
{
    protected static string $resource = MediaResource::class;

    protected static string $view = 'filament.resources.media-resource.pages.list-media';

    public string $activeTab = 'gambar';

    public function mount(): void
    {
        $this->activeTab = request()->get('tab', 'gambar');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getTitle(): string
    {
        return 'Media';
    }

    public function getMediaFiles(): array
    {
        $disk = Storage::disk('public');
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
        $docExtensions  = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip'];

        $imageDirs = [
            'news/thumbnails',
            'gtk',
            'facilities',
            'banners',
            'banners/default',
            'achievements',
            'extracurriculars',
            'testimonials',
            'pages',
            'popups',
            'portal-apps',
            'infografis',
            'gallery',
            'alumni-coordinators',
            'badges',
            'headmaster-history',
            'school-leaders',
            'social-media',
            'programs',
        ];

        $docDirs = [
            'archives/files',
            'archives/covers',
        ];

        $images = [];
        $docs   = [];

        // Scan image directories
        foreach ($imageDirs as $dir) {
            if (! $disk->exists($dir)) {
                continue;
            }
            $files = $disk->files($dir);
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $imageExtensions)) {
                    $images[] = [
                        'path'     => $file,
                        'url'      => asset('storage/' . $file),
                        'name'     => basename($file),
                        'dir'      => $dir,
                        'size'     => $disk->size($file),
                        'modified' => $disk->lastModified($file),
                    ];
                }
            }
        }

        // Scan doc directories
        foreach ($docDirs as $dir) {
            if (! $disk->exists($dir)) {
                continue;
            }
            $files = $disk->files($dir);
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $docExtensions)) {
                    $docs[] = [
                        'path'     => $file,
                        'url'      => asset('storage/' . $file),
                        'name'     => basename($file),
                        'dir'      => $dir,
                        'size'     => $disk->size($file),
                        'modified' => $disk->lastModified($file),
                        'ext'      => $ext,
                    ];
                }
            }
        }

        // Sort by modified desc
        usort($images, fn($a, $b) => $b['modified'] <=> $a['modified']);
        usort($docs,   fn($a, $b) => $b['modified'] <=> $a['modified']);

        return [
            'images' => $images,
            'docs'   => $docs,
        ];
    }

    public function getGalleryPosts()
    {
        return GalleryPost::with(['photos', 'news'])->latest()->get();
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
