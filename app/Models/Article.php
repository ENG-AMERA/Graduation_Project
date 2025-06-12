<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
        protected $fillable=[
        'content','pharmacist_id','image','like','dislike','topic'
    ];

    public function pharmacist(){
      return $this->belongsTo(Pharmacist::class);
    }


}
