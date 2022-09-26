<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'create_at',
        'update_at',
    ];

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
