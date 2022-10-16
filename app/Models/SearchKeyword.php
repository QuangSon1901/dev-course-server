<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchKeyword extends Model
{
    use HasFactory;

    protected $table = 'search_keywords';

    protected $fillable = [
        'keyword',
        'program_id',
        'category_course_id',
        'topic_course_id',
        'teacher_id',
        'create_at',
        'update_at',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function programs()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function category_courses()
    {
        return $this->belongsTo(CategoryCourse::class, 'category_course_id');
    }

    public function topic_courses()
    {
        return $this->belongsTo(TopicCourse::class, 'topic_course_id');
    }

    public function teachers()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
