<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppBackup extends Model
{
    protected $fillable = [
        'filename', 'type', 'status', 'size_bytes', 'app_version',
        'error', 'triggered_by', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function formattedSize(): string
    {
        if (! $this->size_bytes) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = (float) $this->size_bytes;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
