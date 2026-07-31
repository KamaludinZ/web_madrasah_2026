<?php

namespace App\Models;

/**
 * Model Saran — extend Complaint, tabel complaints, type='saran'.
 * Filtering type dilakukan via getEloquentQuery() di SaranResource,
 * bukan global scope, agar tidak konflik dengan Filament/Livewire.
 */
class Saran extends Complaint
{
    protected $table = 'complaints';

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->type          = 'saran';
            $model->ticket_number = 'SAR-' . date('Ymd') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        });
    }
}
