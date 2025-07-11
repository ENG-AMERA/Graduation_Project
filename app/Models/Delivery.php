<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','delivery_method','number_method','accept'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function deliveryRequests()
   {
    return $this->hasMany(DeliveryRequest::class);
   }

      public function applycartorder(){
        return $this->hasMany(ApplyCartOrder::class);
    }

}
