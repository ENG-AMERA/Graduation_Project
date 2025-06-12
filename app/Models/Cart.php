<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

         protected $fillable=[
        'user_id','totalprice'
    ];


        public function cart_item(){
        return $this->hasMany(Cart_Item::class);
     }

         public function user()
    {
        return $this->belongsTo(User::class);
    }
}

