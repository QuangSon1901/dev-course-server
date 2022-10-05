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
        'description',
        'image',
        'price',
        'program_id',
        'slug',
        'create_at',
        'update_at',
    ];

    public function programs()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function class_rooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
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
