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
        'quantity_minimum',
        'quantity_maxnimum',
        'slug',
        'subject_id',
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

    public function subjects()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
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
}
