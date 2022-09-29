<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'name',
        'email',
        'image',
        'create_at',
        'update_at',
    ];
    
    public function courses()
    {
        return $this->hasMany(Courses::class);
    }
}
