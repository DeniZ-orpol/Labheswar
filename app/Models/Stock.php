<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $table = 'purchase';

    protected $fillable = [
        'id',
        'chalan_id',
        'product_id',
        'user_id',
        'bill_no',
        'branch_id',
        'date',
        'product_id',
        'mrp',
        'box',
        'pcs',
        'amount',
    ];
}
