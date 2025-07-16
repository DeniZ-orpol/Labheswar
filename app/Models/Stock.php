<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $table = 'stocks';

    protected $fillable = [
        'id',
        'chalan_id',
        'product_id',
        'user_id',
        'bill_no',
        'branch_id',
        'date',
        'product_id',
        'prate',
        'box',
        'pcs',
        'amount',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
