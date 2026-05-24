<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Program extends Model {
    protected $fillable = ['type', 'name', 'description', 'cover', 'order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function getCoverUrlAttribute(): string { return $this->cover ? asset('storage/' . $this->cover) : asset('images/program-placeholder.jpg'); }
}
