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
            ->saveSlugsTo('slug');
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function getNewsCountAttribute(): int
    {
        return $this->news()->published()->count();
    }
}
