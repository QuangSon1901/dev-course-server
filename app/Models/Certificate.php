<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates';

    protected $fillable = [
        'date',
        'verifier',
        'verification_place',
        'create_at',
        'update_at',
    ];

    public function classes_users()
    {
        return $this->hasMany(ClassesUsers::class);
    }
}
