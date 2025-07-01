<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInHand extends Model
{
    protected $table = 'stock_in_hands';
    protected $fillable = [
        'product_id',
        'price',
        'qty_in_hand',
        'qty_sold',
        'inventory_value',
        'sale_value',
        'available_stock',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
