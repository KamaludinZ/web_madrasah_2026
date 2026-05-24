<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Testimonial extends Model {
    protected $fillable = ['name', 'graduation_year', 'content', 'photo', 'current_school', 'order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function getPhotoUrlAttribute(): ?string { return $this->photo ? asset('storage/' . $this->photo) : null; }
}
