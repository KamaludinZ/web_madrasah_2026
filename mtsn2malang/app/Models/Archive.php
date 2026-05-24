<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $fillable = [
        'type', 'title', 'cover', 'category', 'published_date',
        'file_path', 'external_link', 'link_type', 'view_count', 'is_active'
    ];

    protected $casts = [
        'published_date' => 'date',
        'is_active' => 'boolean',
        'view_count' => 'integer',
    ];

    public function getCoverUrlAttribute(): string
    {
        if ($this->cover) return asset('storage/' . $this->cover);
        return asset('images/archive-placeholder.jpg');
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->link_type === 'file' && $this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return $this->external_link;
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type)->where('is_active', true)->orderByDesc('published_date');
    }

    public function incrementViewCount(): void
    {
        $sessionKey = 'archive_viewed_' . $this->id;
        if (!session()->has($sessionKey)) {
            $this->increment('view_count');
            session()->put($sessionKey, true);
        }
    }
}
