<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryPost extends Model
{
    use HasFactory;

    protected $table = 'gallery_posts';

    protected $fillable = [
        'news_id',
        'title',
        'description',
    ];

    public function news()
    {
        return $this->belongsTo(News::class);
    }

    public function photos()
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('order');
    }

    public function getCoverPhotoAttribute(): ?GalleryPhoto
    {
        return $this->photos()->first();
    }
}
