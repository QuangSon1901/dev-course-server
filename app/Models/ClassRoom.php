<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class ClassRoom extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'class_rooms';

    protected $fillable = [
        'name',
        'quantity_minimum',
        'quantity_maxnimum',
        'slug',
        'course_id',
        'room_id',
        'teacher_id',
        'time_frame_id',
        'week_day_id',
        'create_at',
        'update_at',
    ];

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

    public function rooms()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function courses()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function teachers()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function time_frames()
    {
        return $this->belongsTo(TimeFrame::class, 'time_frame_id');
    }

    public function week_days()
    {
        return $this->belongsTo(WeekDay::class, 'week_day_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'classes_users', 'class_id', 'user_id')->withPivot('date','money');
    }
}
