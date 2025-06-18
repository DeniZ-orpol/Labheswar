<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppCartsOrders extends Model
{
    protected $table = 'app_carts_orders';

    protected $fillable = [
        'branch_user_id',
        'cart_id',
        'product_id',
        'firm_id',
        'product_weight',
        'product_price',
        'product_quantity',
        'taxes',
        'sub_total',
        'total_amount',
        'gst',
        'gst_p',
        'return_product'
    ];

    public function branchUser()
    {
        return $this->belongsTo(BranchUsers::class, 'branch_user_id');
    }

    public function cart()
    {
        return $this->belongsTo(AppCarts::class, 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
