<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Comment extends Model {
    use SoftDeletes;
    protected $fillable = ['news_id', 'user_id', 'guest_name', 'guest_email', 'content', 'status'];
    public function news() { return $this->belongsTo(News::class); }
    public function user() { return $this->belongsTo(User::class); }
}
