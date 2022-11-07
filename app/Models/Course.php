<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Course extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'courses';

    protected $fillable = [
        'name',
        'sub_name',
        'video_demo',
        'description',
        'objectives',
        'total_lesson',
        'image',
        'price',
        'form_of_learning',
        'level',
        'slug',
        'topic_course_id',
        'create_at',
        'update_at',
    ];

    public function search_keywords()
    {
        return $this->belongsToMany(SearchKeyword::class, 'search', 'course_id', 'search_keyword_id');
    }

    public function topic_courses()
    {
        return $this->belongsTo(TopicCourse::class, 'topic_course_id');
    }

    public function class_rooms()
    {
        return $this->hasMany(ClassRoom::class)->with('rooms', 'time_frames', 'week_days')->orderBy('opening_day');
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function users_wish()
    {
        return $this->belongsToMany(User::class, 'wish_courses', 'course_id', 'user_id');
    }

    public function users_review()
    {
        return $this->belongsToMany(ReviewCourse::class, 'review_courses', 'course_id', 'user_id');
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
