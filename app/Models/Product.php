<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasDynamicTable;

    protected $table = 'products';

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
        // 'gst_active',
        'converse_carton',
        'carton_barcode',
        'converse_box',
        'box_barcode',
        'converse_pcs',
        'negative_billing',
        'min_qty',
        'reorder_qty',
        'discount',
        'max_discount',
        'discount_scheme',
        'bonus_use',
    ];

    public function pCompany()
    {
        return $this->belongsTo(Company::class, 'company');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function hsnCode()
    {
        return $this->belongsTo(HsnCode::class, 'hsn_code_id');
    }
}
