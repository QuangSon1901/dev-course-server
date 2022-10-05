<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'room',
        'address',
        'create_at',
        'update_at',
    ];

    public function class_rooms()
    {
        return $this->hasMany(ClassRoom::class);
    }
}
