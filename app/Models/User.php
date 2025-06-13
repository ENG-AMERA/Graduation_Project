<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */



     /**
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 */

    protected $fillable = [
        'firstname',
        'email',
        'password',
        'lastname',
        'age',
        'location',
        'phone',
        'gender',
        'points'
    ];

    public function roles()
    {
        return $this->hasMany(Role::class);
    }


    public function orders()
     {
    return $this->hasMany(Order::class);
     }

        public function cart()
    {
        return $this->hasMany(Cart::class);
    }


  public function pharmacist()
    {
        return $this->hasOne(Pharmacist::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }


public function pharmas()
{
    return $this->belongsToMany(Pharma::class)
                ->using(PharmaUser::class)
                ->withPivot('type', 'reason')
                ->withTimestamps();
}

     public function recommendation(){
     return $this->hasMany(Recommendation::class);
}


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
}
