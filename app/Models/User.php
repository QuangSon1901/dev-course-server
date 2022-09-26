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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'role_id',
        'name',
        'sex',
        'birth',
        'email',
        'password',
        'create_at',
        'update_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    // public function tokenable()
    // {
    //     return $this->morphTo('tokenable');
    // }

    // public static function findToken($token)
    // {
    //     if (strpos($token, '|') === false) {
    //         return static::where('token', hash('sha256', $token))->first();
    //     }

    //     [$id, $token] = explode('|', $token, 2);

    //     if ($instance = static::find($id)) {
    //         return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
    //     }
    // }
}
