<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasDynamicTable;

    protected $table = 'products';

    protected $fillable = [
        'reference_id',
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
        'product_type',
        'weight_to',
        'weight_from',
        'price_1',
        'price_2',
        'price_3',
        'price_4',
        'price_5',
        'kg_1',
        'kg_2',
        'kg_3',
        'kg_4',
        'kg_5',
        'use_static_variant',
        'packaging',
        'packaging_btn',
        'custom_variant',
        'use_custom_variant',
        'loose_below_weight',
        'loose_below_price',
        'auto_variants_in_weight_btn',
        'auto_variants_in_weight',
        'auto_variants_in_amount_btn',
        'auto_variants_in_amount',
        'custom_variant_btn',
        'is_variant',
        'custom_price_btn',
        'custom_price'
    ];

    protected $casts = [
        'auto_variants_in_weight' => 'array',
        'auto_variants_in_amount' => 'array',
        'custom_price' => 'array',
    ];

    public $timestamps = true;

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

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'product_id', 'id');
    }

    public function variants()
    {
        return $this->hasMany(Product::class, 'reference_id');
    }
    public function decimalPackaging()
    {
        return $this->belongsTo(Packaging::class, 'packaging', 'group_id');
    }
}
