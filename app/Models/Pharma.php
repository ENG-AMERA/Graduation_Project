<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharma extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','license','phone','length','width'
    ];

        public function pharmacists()
    {
        return $this->hasOne(Pharmacist::class);
    }

    public function users()
{
    return $this->belongsToMany(User::class)
                ->using(PharmaUser::class)
                ->withPivot('type', 'reason')
                ->withTimestamps();
}


public function pharmaUsers()
{
    return $this->hasMany(PharmaUser::class);
}

       public function products(){
        return $this->hasMany(Product::class);
     }

        public function cart(){
        return $this->hasMany(Cart::class);
     }

           public function cartorder(){
        return $this->hasMany(CartOrder::class);
     }

}
