<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'description',
        'create_at',
        'update_at',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
