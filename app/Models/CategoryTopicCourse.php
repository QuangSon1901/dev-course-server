<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTopicCourse extends Model
{
    use HasFactory;

    protected $table = 'category_topic_courses';

    protected $fillable = [
        'category_course_id',
        'topic_course_id',
        'create_at',
        'update_at',
    ];

    public function category_courses()
    {
        return $this->belongsTo(CategoryCourse::class, 'category_course_id');
    }

    public function topic_courses()
    {
        return $this->belongsTo(TopicCourse::class, 'topic_course_id');
    }
}
