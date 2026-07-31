<?php

namespace App\Models;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;

class SocialMediaContent extends Model
{
    protected $fillable = [
        'platform',
        'title',
        'post_url',
        'embed_url',
        'thumbnail',
        'order',
        'is_active',
        'show_on_madrasah',
        'show_on_mahad',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_madrasah' => 'boolean',
        'show_on_mahad' => 'boolean',
    ];

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }

        $defaultByPlatform = match ($this->platform) {
            'youtube' => Setting::get('default_social_thumbnail_youtube'),
            'instagram' => Setting::get('default_social_thumbnail_instagram'),
            'tiktok' => Setting::get('default_social_thumbnail_tiktok'),
            default => null,
        };

        if (!empty($defaultByPlatform)) {
            return asset('storage/' . $defaultByPlatform);
        }

        $fromLink = $this->getThumbnailFromLink();

        return $fromLink ?: asset('images/program-placeholder.jpg');
    }

    protected function getThumbnailFromLink(): ?string
    {
        $url = (string) ($this->post_url ?? '');

        if ($url === '') {
            return null;
        }

        if ($this->platform === 'youtube') {
            if (preg_match('/(?:v=|youtu\.be\/)([A-Za-z0-9_-]{6,})/', $url, $matches)) {
                return 'https://i.ytimg.com/vi/' . $matches[1] . '/hqdefault.jpg';
            }

            return null;
        }

        if (in_array($this->platform, ['instagram', 'tiktok'], true)) {
            return 'https://www.google.com/s2/favicons?domain=' . parse_url($url, PHP_URL_HOST) . '&sz=256';
        }

        return null;
    }
}
