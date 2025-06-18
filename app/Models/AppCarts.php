<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppCarts extends Model
{
    protected $table = 'app_carts';

    protected $fillable = [
        'cart_id',
        'branch_user_id',
        'sub_total',
        'texes',
        'total_amount',
    ];

    public function branchUser() {
        return $this->belongsTo(BranchUsers::class, 'branch_user_id');
    }
}
