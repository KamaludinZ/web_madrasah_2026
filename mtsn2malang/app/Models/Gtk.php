<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Gtk extends Model {
    protected $table = 'gtk';
    protected $fillable = ['name', 'nip', 'position', 'type', 'subject', 'education', 'photo', 'order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function getPhotoUrlAttribute(): string { return $this->photo ? asset('storage/' . $this->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1a7a3c&color=fff&size=200'; }
}
