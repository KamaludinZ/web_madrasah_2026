<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Facility extends Model {
    protected $fillable = ['name', 'description', 'image', 'category', 'order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function getImageUrlAttribute(): string { return $this->image ? asset('storage/' . $this->image) : asset('images/facility-placeholder.jpg'); }
}
