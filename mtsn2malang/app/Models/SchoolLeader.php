<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolLeader extends Model
{
    protected $fillable = [
        'photo', 'name', 'position', 'nip', 'period', 'order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
