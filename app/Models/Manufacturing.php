<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturing extends Model
{
    protected $fillable = [
        'recipe_id',
        'product_id',
        'qty',
        'unit'
    ];

    protected $casts = [
        'ingredients' => 'array',
    ];

    public function recipe() {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
