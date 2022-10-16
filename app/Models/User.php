<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'avatar',
        'sex',
        'birth',
        'email',
        'password',
        'create_at',
        'update_at',
    ];

 
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function class_rooms()
    {
        return $this->belongsToMany(ClassRoom::class, 'classes_users', 'user_id', 'class_id')->withPivot('date','price','status', 'certificate_id');
    }

    public function wish_courses()
    {
        return $this->belongsToMany(Course::class, 'wish_courses', 'user_id', 'course_id');
    }

    public function review_courses()
    {
        return $this->belongsToMany(Course::class, 'review_courses', 'user_id', 'course_id')->withPivot('comment','rating');
    }
}
