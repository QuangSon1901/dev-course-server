<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Lesson extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'lessons';

    protected $fillable = [
        'name',
        'description',
        'image',
        'video_demo',
        'video_lectures',
        'z_index',
        'unit_id',
        'slug',
    ];

    public function units()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
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
