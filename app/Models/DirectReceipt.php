<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectReceipt extends Model
{
    protected $fillable = [
        'ledger',
        'dr_no',
        'date',
        'products',
        'total_amount',
    ];

    protected $casts = [
        'products' => 'array',
        'date' => 'date',
    ];

     public function ledger()
    {
        return $this->belongsTo(PurchaseParty::class, 'ledger');
    }
}
