<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OneToMany extends Model
{
    protected $table = 'one_to_many';

    protected $fillable = [
        'ledger_id',
        'date',
        'entry_no',
        'qty',
        'raw_item',
        'item_to_create'
    ];

    public $timestamps = true; 

    public function ledger() {
        return $this->belongsTo(PurchaseParty::class, 'ledger_id');
    }

    public function rawItem() {
        return $this->belongsTo(Product::class, 'raw_item');
    }

    public function productToCreate() {
        return $this->belongsTo(Product::class);
    }
}
