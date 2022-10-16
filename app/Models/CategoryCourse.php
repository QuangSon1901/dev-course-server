<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryCourse extends Model
{
    use HasFactory;

    protected $table = 'category_courses';

    protected $fillable = [
        'name',
        'description',
        'image',
        'slug',
        'program_id',
        'create_at',
        'update_at',
    ];

    public function programs()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function search_keywords()
    {
        return $this->hasMany(SearchKeyword::class);
    }

    public function topic_courses()
    {
        return $this->belongsToMany(TopicCourse::class, 'category_topic_courses', 'category_course_id', 'topic_course_id');
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
