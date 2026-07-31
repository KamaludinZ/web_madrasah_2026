<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadmasterHistory extends Model
{
    protected $fillable = ['name', 'nip', 'period', 'photo', 'order'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}
