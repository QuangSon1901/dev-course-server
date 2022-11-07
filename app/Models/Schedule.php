<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedule';

    protected $fillable = [
        'date_learn',
        'lesson',
        'class_id',
        'create_at',
        'update_at',
    ];

    public function class_rooms()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }
}
