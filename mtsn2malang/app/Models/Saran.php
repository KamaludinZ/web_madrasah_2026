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
            $model->ticket_number = static::generateTicketNumber('SAR');
        });
    }
}
