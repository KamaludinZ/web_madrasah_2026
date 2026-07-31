<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WordpressMigrationBatch extends Model
{
    protected $fillable = [
        'mode',
        'status',
        'source_file',
        'source_database',
        'source_prefix',
        'migrated_posts',
        'handled_images',
        'started_at',
        'finished_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'migrated_posts' => 'integer',
        'handled_images' => 'integer',
    ];
}
