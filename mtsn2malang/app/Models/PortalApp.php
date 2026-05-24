<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PortalApp extends Model {
    protected $fillable = ['name', 'logo', 'link', 'description', 'order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function getLogoUrlAttribute(): string { return $this->logo ? asset('storage/' . $this->logo) : asset('images/app-placeholder.png'); }
}
