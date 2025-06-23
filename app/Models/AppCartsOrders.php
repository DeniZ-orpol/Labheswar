<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppCartsOrders extends Model
{
    protected $table = 'app_cart_order';

    protected $fillable = [
        'order_receipt_id',
        'user_id',
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
        'return_product',
    ];

    public function orderReceipt() {
        return $this->belongsTo(AppCartsOrderBill::class, 'order_receipt_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
