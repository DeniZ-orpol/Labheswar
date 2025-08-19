<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stockreceipt extends Model
{
    protected $fillable = [
        'ledger',
        'entry_no',
        'date',
        'products',
        'total_amount',
        'status',
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
