<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Infografis extends Model {
    protected $fillable = ['title', 'image', 'order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function getImageUrlAttribute(): string { return $this->image ? asset('storage/' . $this->image) : asset('images/infografis-placeholder.jpg'); }
}
