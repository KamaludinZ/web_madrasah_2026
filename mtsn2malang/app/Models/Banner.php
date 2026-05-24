<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Banner extends Model {
    protected $fillable = ['title', 'subtitle', 'description', 'image', 'link', 'order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function getImageUrlAttribute(): string { return $this->image ? asset('storage/' . $this->image) : asset('images/banner-placeholder.jpg'); }
}
