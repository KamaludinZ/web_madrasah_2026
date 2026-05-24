<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PageContent extends Model {
    protected $fillable = ['page_key', 'title', 'content', 'image', 'extra_data'];
    protected $casts = ['extra_data' => 'array'];
    public function getImageUrlAttribute(): ?string { return $this->image ? asset('storage/' . $this->image) : null; }
}
