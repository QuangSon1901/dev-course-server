<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class TopicCourse extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'topic_courses';

    protected $fillable = [
        'name',
        'description',
        'image',
        'slug',
        'create_at',
        'update_at',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function search_keywords()
    {
        return $this->hasMany(SearchKeyword::class);
    }

    public function category_courses()
    {
        return $this->belongsToMany(CategoryCourse::class, 'category_topic_courses', 'topic_course_id', 'category_course_id');
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
