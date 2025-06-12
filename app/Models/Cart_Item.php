<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart_Item extends Model
{
    use HasFactory;
            protected $fillable=[
        'product_id','cart_id','type_id','totalprice','quantity'
    ];


         public function product()
    {
        return $this->belongsTo(Product::class);
    }

         public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

         public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
