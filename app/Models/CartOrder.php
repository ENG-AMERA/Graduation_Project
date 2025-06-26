<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartOrder extends Model
{
    use HasFactory;

         protected $fillable=[
        'user_id','totalprice','length','width','deliverydate','done',
        'pharma_id','deliveryprice','verified','accepted'];

    public function user(){
        return $this->belongsTo(User::class);
    }

       public function pharma(){
        return $this->belongsTo(Pharma::class);
    }

    public function cartorderitem(){
        return $this->hasMany(CartOrderItem::class);
    }

    public function applycartorder(){
        return $this->hasOne(ApplyCartOrder::class);
    }

}
