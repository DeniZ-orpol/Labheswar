<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_name',
        'barcode',
        'image',
        'search_option',
        'unit_types',
        'decimal_btn',
        'company',
        'category_id',
        'hsn_code_id',
        'sgst',
        'cgst1',
        'cgst2',
        'cess',
        'mrp',
        'purchase_rate',
        'sale_rate_a',
        'sale_rate_b',
        'sale_rate_c',
        'sale_online',
        'gst_active',
        'converse_carton',
        'converse_box',
        'converse_pcs',
        'negative_billing',
        'min_qty',
        'reorder_qty',
        'discount',
        'max_discount',
        'discount_scheme',
        'bonus_use',
    ];

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function hsnCode() {
        return $this->belongsTo(HsnCode::class);
    }
}
