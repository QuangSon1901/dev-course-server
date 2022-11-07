<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;

    protected $table = 'class_rooms';

    protected $fillable = [
        'class_id',
        'quantity_minimum',
        'quantity_maxnimum',
        'opening_day',
        'estimated_end_time',
        'status',
        'course_id',
        'room_id',
        'teacher_id',
        'create_at',
        'update_at',
    ];

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

    public function schedule()
    {
        return $this->hasMany(Schedule::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'classes_users', 'class_id', 'user_id')->withPivot('vendor_order_id', 'date','price','status', 'certificate_id');
    }
}
