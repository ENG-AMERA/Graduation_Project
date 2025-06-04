<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmaUser extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'pharma_id',
        'order_id',
        'type',
        'reason',
        'accept_user',
        'accept_pharma'
    ];
    public function order()
{
    return $this->belongsTo(Order::class);
}

public function deliveryRequest()
{
    return $this->hasOne(DeliveryRequest::class);
}

}
