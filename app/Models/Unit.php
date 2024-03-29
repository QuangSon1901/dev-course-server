<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Unit extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'units';

    protected $fillable = [
        'name',
        'description',
        'image',
        'z_index',
        'course_id',
        'slug',
        'create_at',
        'update_at',
    ];

    public function courses()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
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
