<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Program extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'programs';

    protected $fillable = [
        'name',
        'description',
        'image',
        'slug',
        'create_at',
        'update_at',
    ];

    public function category_courses()
    {
        return $this->hasMany(CategoryCourse::class);
    }

    public function search_keywords()
    {
        return $this->hasMany(SearchKeyword::class);
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
