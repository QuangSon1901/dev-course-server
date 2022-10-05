<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassesUsers extends Model
{
    use HasFactory;

    protected $table = 'classes_users';

    protected $fillable = [
        'user_id',
        'class_id',
        'date',
        'money',
        'create_at',
        'update_at',
    ];
}
