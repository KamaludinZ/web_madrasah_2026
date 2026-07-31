<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniCoordinator extends Model
{
    protected $fillable = [
        'angkatan',
        'nama_koordinator',
        'kontak',
        'foto',
        'deskripsi',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}
