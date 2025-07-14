<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasDynamicTable;

    protected $table = 'inventory';
    protected $fillable = ['product_id', 'type', 'quantity', 'unit', 'reason', 'gst', 'mrp', 'sale_price', 'purchase_price','purchase_id'];

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
