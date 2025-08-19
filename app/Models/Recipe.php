<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'product_id',
        'ingredients'
    ];

     protected $casts = [
        'ingredients' => 'array',
    ];

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }
}
