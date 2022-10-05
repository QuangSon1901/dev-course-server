<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekDay extends Model
{
    use HasFactory;

    protected $table = 'week_days';

    protected $fillable = [
        'week_day',
        'create_at',
        'update_at',
    ];

    public function class_rooms()
    {
        return $this->hasMany(ClassRoom::class);
    }
}
