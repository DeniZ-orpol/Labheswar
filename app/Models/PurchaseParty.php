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
        'gst_heading',
        'mobile_no',
        'email',
        'address',
        'station',
        'state',
        'acc_no',
        'ifsc_code',
        'pincode',
        'ledger_group',
        'balancing_method',
        'mail_to',
        'contact_person',
        'designation',
        'note',
        'ledger_category',
        'country',
        'pan_no'
    ];
}
