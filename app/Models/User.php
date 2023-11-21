<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'is_verified',
        'location_id',
        'type',
        'description',
        'category',
        'avatar',
        'external_id',
        'external_auth',
        'score',
        'total_operations',
        'total_score'

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

///relacion entre user a pack
    public function pack(){
        return $this->hasMany(Pack::class);
    }

//Agrege esta funcion para relacionar la location con el user y poder obtenerlo para mostarlo en getAllUsers
      public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

///relacion entre user a pack
    public function Favorite(){
        return $this->hasMany(Favorite::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }
}
