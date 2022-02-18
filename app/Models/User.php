<?php

namespace App\Models;

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
        'user_name',
        'user_lastName',
        'email',
        'user_document',
        'password',
        'user_phone',
        'updated_at',
        'user_address'
    ];

    protected $table = 'user';
    protected $primaryKey = 'id_user';

    const CREATED_AT = null;
    //const UPDATED_AT = null;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:Y-m-d H:i:s' ,
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'username' => 'user_name',
    ];


    public function roleUsers(){
        return $this->hasOne(RoleUser::class, 'id_role');
    }

    public function productos(){
        return $this->hasMany(Producto::class);
    }
}
