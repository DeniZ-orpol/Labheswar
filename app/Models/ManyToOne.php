<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManyToOne extends Model
{
    protected $table = 'many_to_one';

    protected $fillable = [
        'ledger_id',
        'date',
        'entry_no',
        'conversion_item',
        'qty',
        'raw_item',
    ];

    public $timestamps = true;

    public function ledger()
    {
        return $this->belongsTo(PurchaseParty::class, 'ledger_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'conversion_item');
    }
}
