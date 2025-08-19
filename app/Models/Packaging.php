<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'group',
        'product_id',
        'weight_from',
        'weight_to',
    ];

    public $timestamps = true;

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }

}
