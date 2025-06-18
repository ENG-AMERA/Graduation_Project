<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
        protected $fillable=[
        'name','description','price','quantity','evaluation','category_id',
        'image','pharma_id','has_variants','people','finalevaluation'
    ];

          public function category()
    {
        return $this->belongsTo(Category::class);
    }
          public function pharma()
    {
        return $this->belongsTo(Pharma::class);
    }
     public function types(){
        return $this->hasMany(Type::class);
     }

           public function cart_item(){
        return $this->hasMany(Cart_Item::class);
     }

    public function cartorderitem(){
        return $this->hasMany(CartOrderItem::class);
     }

     public function recommendation(){
     return $this->hasMany(Recommendation::class);
}

}
