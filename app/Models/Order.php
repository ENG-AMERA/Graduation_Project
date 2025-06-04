<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
    'user_id',
    'name_medicine',
    'photo',
    'length',
    'width',
    'type',
    'time',
];


public function user()
{
    return $this->belongsTo(User::class);
}

}
