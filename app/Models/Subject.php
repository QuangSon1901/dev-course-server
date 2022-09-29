<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Subject extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'subjects';

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

    public function courses()
    {
        return $this->hasMany(Courses::class);
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
