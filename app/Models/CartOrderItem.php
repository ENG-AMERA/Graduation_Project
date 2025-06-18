<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartOrderItem extends Model
{
    use HasFactory;

        protected $fillable=[
        'product_id','cart_order_id','type_id','totalprice','quantity',
    ];


     public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function cartorder()
    {
        return $this->belongsTo(CartOrder::class);
    }





}

