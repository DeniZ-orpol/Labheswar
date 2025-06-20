<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppCartsOrderBill extends Model
{
    protected $table = 'app_cart_order_bill';

    protected $fillable = [
        'cart_id',
        'total_texes',
        'sub_total',
        'total',
        'customer_name',
        'customer_contact',
        'razorpay_payment_id',
        'bill_due_date',
        'payment_status',
        'status',
        'user_id',
        'discount_rs',
        'discount_percentage',
        'return_order',
        'is_delivery',
        'address_id',
        'ship_to_name',
        'expected_delivery_date',
    ];

    public function cart(){
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'cart_id');
    }
}
