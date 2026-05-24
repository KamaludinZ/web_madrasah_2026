<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Extracurricular extends Model {
    protected $fillable = ['name', 'description', 'photo', 'schedule', 'coach', 'order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function getPhotoUrlAttribute(): string { return $this->photo ? asset('storage/' . $this->photo) : asset('images/ekskul-placeholder.jpg'); }
}
