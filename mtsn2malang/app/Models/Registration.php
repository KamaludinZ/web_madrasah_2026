<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Registration extends Model {
    protected $fillable = ['type', 'schedule', 'general_requirements', 'specific_requirements', 'brochure_file', 'brochure_link', 'registration_link', 'is_active'];
    protected $casts = ['schedule' => 'array', 'is_active' => 'boolean'];
    public function getBrochureUrlAttribute(): ?string { return $this->brochure_file ? asset('storage/' . $this->brochure_file) : $this->brochure_link; }
}
