<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseParty extends Model
{
    protected $table = 'purchase_party';

    protected $fillable = ['party_name'];
}
