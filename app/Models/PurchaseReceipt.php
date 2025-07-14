<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceipt extends Model
{
    use HasDynamicTable;
    protected $table = 'purchase_receipt';

    protected $fillable = [
        'bill_date',
        'purchase_party_id',
        'bill_no',
        'delivery_date',
        'gst_status',
        'subtotal',
        'total_discount',
        'total_gst_amount',
        'total_amount',
        'receipt_status',
        'created_by',
        'updated_by'
    ];

    public function purchaseParty(){
        return $this->belongsTo(PurchaseParty::class, 'purchase_party_id');
    }
    

    public function createUser(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updateUser(){
        return $this->belongsTo(User::class, 'updated_by');
    }
}
