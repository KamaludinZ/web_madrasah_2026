<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupItem extends Model
{
    protected $fillable = [
        'popup_id', 'item_type', 'image', 'image_link',
        'content', 'caption', 'order',
    ];

    public function popup(): BelongsTo
    {
        return $this->belongsTo(Popup::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
