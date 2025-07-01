<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitAndLoose extends Model
{
    protected $table = 'profit_and_looses';

    protected $fillable = [
        'amount',
        'type', // 'profit' or 'loss'
        'description',
        'status',
    ];
}
