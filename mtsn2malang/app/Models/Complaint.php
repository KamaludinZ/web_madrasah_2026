<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'ticket_number', 'type', 'name', 'email', 'phone',
        'subject', 'content', 'attachment', 'status', 'response', 'public_response', 'responded_at'
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->ticket_number = static::generateTicketNumber('ADU');
        });
    }

    /**
     * Nomor tiket acak 8 karakter (±1 triliun kombinasi per hari) — format
     * lama 4 digit (9.999 kemungkinan per hari) mudah ditebak sehingga isi
     * laporan whistleblowing orang lain bisa dilacak. Dicek unik agar tidak
     * bentrok dengan tiket yang sudah ada.
     */
    public static function generateTicketNumber(string $prefix): string
    {
        // Tanpa huruf/angka yang mirip (0/O, 1/I/L) agar mudah diketik ulang.
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $random = '';
            for ($i = 0; $i < 8; $i++) {
                $random .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $ticket = $prefix . '-' . date('Ymd') . '-' . $random;
        } while (Complaint::where('ticket_number', $ticket)->exists());

        return $ticket;
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }
}
