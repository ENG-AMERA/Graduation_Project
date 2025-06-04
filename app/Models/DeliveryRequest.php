<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRequest extends Model
{
    use HasFactory;

        protected $fillable = [
        'qr', 'price', 'pharma_user_id', 'delivery_id','done'
    ];

    public function pharmaUser()
    {
        return $this->belongsTo(PharmaUser::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
