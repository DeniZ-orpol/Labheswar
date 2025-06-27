<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class HsnCode extends Model
{
    use HasDynamicTable;
    protected $table = 'hsn_codes';
    protected $fillable = ['hsn_code','gst','short_name'];
}
