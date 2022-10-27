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
        'vendor_order_id',
        'date',
        'price',
        'status',
        'certificate_id',
        'create_at',
        'update_at',
    ];

    public function certificates()
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function class_rooms()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }
}
