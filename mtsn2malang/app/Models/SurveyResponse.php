<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SurveyResponse extends Model {
    protected $fillable = ['type', 'respondent_name', 'respondent_type', 'answers', 'ip_address'];
    protected $casts = ['answers' => 'array'];
}
