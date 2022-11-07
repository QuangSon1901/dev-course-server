<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'name',
        'email',
        'image',
        'topic_course_id',
        'create_at',
        'update_at',
    ];
    
    public function class_rooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function topic_courses()
    {
        return $this->belongsTo(TopicCourse::class, 'topic_course_id');
    }
}
