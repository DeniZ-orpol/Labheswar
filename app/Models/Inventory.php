<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasDynamicTable;

    protected $table = 'inventory';
    protected $fillable = [
        'product_id',
        'purchase_id',
        'chalan_id',
        'type',
        'quantity',
        'total_qty',
        'unit',
        'reason',
        'gst',
        'gst_p',
        'mrp',
        'sale_price',
        'purchase_price',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
}
