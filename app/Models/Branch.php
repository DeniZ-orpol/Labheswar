<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'location',
        'latitude',
        'longitude',
        'gst_no',
        'branch_admin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
