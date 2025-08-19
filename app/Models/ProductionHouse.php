<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionHouse extends Model
{
    protected $table = 'production_house';

    protected $fillable = [
        'ledger',
        'product_id',
        'mrp',
        'sale_rate',
        'qty',
        'amount',
        'type',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function ledger()
    {
        return $this->belongsTo(PurchaseParty::class, 'ledger');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
