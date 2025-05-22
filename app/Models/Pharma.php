<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharma extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name','license','phone','length','width'
    ];
    
    public function pharmacists()
    {
        return $this->hasMany(Pharmacist::class);
    }
}
