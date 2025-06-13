<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacist extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate', 'description','license','user_id','pharma_id','accept','accept_point','point_value'
    ];

       public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharma()
    {
        return $this->belongsTo(Pharma::class);
    }

    public function articles(){
        return $this->hasMany(Article::class);
    }
}




