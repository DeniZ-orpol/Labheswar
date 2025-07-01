<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    protected $fillable = [
        'name',
        'station',
        'acc_group',
        'balancing_method',
        'mail_to',
        'address',
        'pin_code',
        'email',
        'website',
        'contact_person',
        'designation',
        'phone_no',
        'gst_no',
        'state',
        'gst_heading',
        'note',
        'ledger_category',
        'country',
        'pan_no',
        'type'
    ];
}
