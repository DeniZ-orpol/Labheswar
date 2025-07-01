<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetails extends Model
{
    protected $fillable = [
        'bank_name',
        'account_no',
        'ifsc_code',
        'close_on',
        'opening_bank_balance'
    ];
}
