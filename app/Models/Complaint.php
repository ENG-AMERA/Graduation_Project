<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

     protected $fillable = ['user_id', 'pharma_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharma()
    {
        return $this->belongsTo(Pharma::class);
    }
}
