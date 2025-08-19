<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{
    //
    protected $table = 'formulas';

    protected $fillable = [
        'product_id',
        'quantity',
        'total_cost',
        'auto_production',
        'ingredients',
    ];

    public $timestamps = true;

     protected $casts = [
        'ingredients' => 'array',
    ];

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }
}
