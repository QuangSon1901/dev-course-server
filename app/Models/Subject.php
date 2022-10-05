<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'subjects';

    protected $fillable = [
        'name',
        'description',
        'image',
        'course_id',
        'slug',
        'create_at',
        'update_at',
    ];

    public function courses()
    {
        return $this->belongsTo(Course::class, 'course_id');
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
