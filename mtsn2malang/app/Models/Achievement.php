<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Achievement extends Model {
    protected $fillable = ['student_name', 'competition_name', 'level', 'rank', 'academic_year', 'achievement_date', 'organizer', 'photo'];
    protected $casts = ['achievement_date' => 'date'];
}
