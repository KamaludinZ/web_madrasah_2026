<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUpdate extends Model
{
    protected $fillable = [
        'from_version', 'to_version', 'status', 'release_tag',
        'backup_filename', 'log', 'error', 'triggered_by',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function appendLog(string $line): void
    {
        $this->log = trim(($this->log ?? '') . "\n[" . now()->format('H:i:s') . "] " . $line);
        $this->save();
    }
}
