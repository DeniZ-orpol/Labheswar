<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopularProducts extends Model
{
    protected $table = 'popular_products';

    protected $fillable = [
        'user_id',
        'product_id',
        'count'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }
}
