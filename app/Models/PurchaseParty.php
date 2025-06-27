<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class PurchaseParty extends Model
{
    use HasDynamicTable;
    protected $table = 'purchase_party';

    protected $fillable = [
        'party_name',
        'company_name',
        'gst_number',
        'acc_no',
        'ifsc_code',
        'station',
        'pincode',
        'mobile_no',
        'email',
        'address'
    ];
}
