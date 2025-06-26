<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplyCartOrder extends Model
{
    use HasFactory;
            protected $fillable=[
        'qr','delivery_id','cart_order_id',
    ];

    public function delivery(){
      return  $this->belongsTo(Delivery::class);
    }
       public function cartorder(){
      return  $this->belongsTo(CartOrder::class);
    }



}
