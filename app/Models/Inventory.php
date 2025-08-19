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
        'one_to_many_id',
        'many_to_one_id',
        'type',
        'total_qty',
        'quantity',
        'unit',
        'reason',
        'gst',
        'gst_p',
        'mrp',
        'sale_price',
        'purchase_price',
    ];

    public $timestamps = true;

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function oneToMany()
    {
        return $this->belongsTo(OneToMany::class, 'one_to_many_id');
    }

    public function manyToOne()
    {
        return $this->belongsTo(OneToMany::class, 'many_to_one_id');
    }
}
