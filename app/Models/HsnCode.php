<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HsnCode extends Model
{
    protected $table = 'hsn_codes';
    protected $fillable = ['hsn_code','gst'];
}
