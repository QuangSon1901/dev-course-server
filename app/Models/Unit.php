<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'name',
        'description',
        'image',
        'course_id',
        'create_at',
        'update_at',
    ];

    public function courses()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
