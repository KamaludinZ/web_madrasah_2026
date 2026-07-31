<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryPhoto extends Model
{
    use HasFactory;

    protected $table = 'gallery_photos';

    protected $fillable = [
        'gallery_post_id',
        'file_path',
        'caption',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function galleryPost()
    {
        return $this->belongsTo(GalleryPost::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
