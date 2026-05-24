<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ServiceStandard extends Model {
    protected $fillable = ['name', 'requirements', 'procedures', 'service_time', 'cost', 'product', 'complaint_handling', 'order', 'is_active'];
    protected $casts = ['requirements' => 'array', 'procedures' => 'array', 'is_active' => 'boolean'];
}
