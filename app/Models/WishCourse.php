<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishCourse extends Model
{
    use HasFactory;

    protected $table = 'wish_courses';

    protected $fillable = [
        'course_id',
        'user_id',
        'create_at',
        'update_at',
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courses()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
