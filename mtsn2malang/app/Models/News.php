<?php

namespace App\Models;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;

class News extends Model
{
    use HasFactory, SoftDeletes, HasSlug, HasTags;

    protected $table = 'news';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'thumbnail',
        'thumbnail_caption',
        'meta_title',
        'meta_description',
        'status',
        'published_at',
        'view_count',
        'is_featured',
        'allow_comments',
        'source',
        'migration_batch_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'view_count' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(200);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->where('status', 'approved');
    }

    public function galleryPost()
    {
        return $this->hasOne(GalleryPost::class);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }

        $defaultNewsThumbnail = Setting::get('default_news_thumbnail');

        if (!empty($defaultNewsThumbnail)) {
            return asset('storage/' . $defaultNewsThumbnail);
        }

        return asset('images/news-placeholder.jpg');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopePopular($query)
    {
        return $query->published()->orderByDesc('view_count');
    }

    public function scopeFeatured($query)
    {
        return $query->published()->where('is_featured', true);
    }

    public function scopeByCategory($query, string $slug)
    {
        return $query->whereHas('category', fn($q) => $q->where('slug', $slug));
    }

    public function incrementViewCount(): void
    {
        $sessionKey = 'news_viewed_' . $this->id;
        if (!session()->has($sessionKey)) {
            $this->increment('view_count');
            session()->put($sessionKey, true);
        }
    }

    public function getExcerptAttribute($value): string
    {
        if ($value) return $value;
        return strip_tags(substr($this->content, 0, 200)) . '...';
    }
}
