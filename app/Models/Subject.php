<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'training_program_id',
        'create_at',
        'update_at',
    ];

    public function training_programs()
    {
        return $this->belongsTo(TrainingProgram::class);
    }
}
