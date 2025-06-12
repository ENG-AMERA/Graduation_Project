<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
        protected $fillable = [
        'product_id', 'name','price','quantity','image'
    ];

     public function product()
    {
        return $this->belongsTo(Product::class);
    }

     public function cart_item()
     {
        return $this->hasMany(Cart_Item::class);
     }
}

