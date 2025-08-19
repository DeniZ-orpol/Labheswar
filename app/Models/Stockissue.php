<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stockissue extends Model
{
    protected $fillable = [
        'ledger',
        'issue_no',
        'date',
        'from_branch',
        'to_branch',
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
