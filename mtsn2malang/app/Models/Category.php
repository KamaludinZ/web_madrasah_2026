<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug', 'description', 'color', 'icon', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function getNewsCountAttribute(): int
    {
        // Pakai hasil withCount('news') bila sudah dimuat — tanpa ini setiap
        // kategori menjalankan satu query COUNT tambahan (N+1).
        if (array_key_exists('news_count', $this->attributes)) {
            return (int) $this->attributes['news_count'];
        }

        return $this->news()->published()->count();
    }
}
