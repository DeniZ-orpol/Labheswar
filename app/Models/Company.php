<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasDynamicTable;
    protected $table = 'companies';
    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class, 'company');
    }
}
