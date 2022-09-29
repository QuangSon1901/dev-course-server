<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeFrame extends Model
{
    use HasFactory;

    protected $table = 'time_frames';

    protected $fillable = [
        'start_time',
        'end_time',
        'create_at',
        'update_at',
    ];

    public function courses()
    {
        return $this->hasMany(Courses::class);
    }
}
