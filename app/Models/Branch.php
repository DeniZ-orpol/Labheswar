<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $connection = 'master';
    protected $fillable = [
        'user_id',
        'name',
        'location',
        'latitude',
        'longitude',
        'gst_no',
        'branch_admin',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    public function user()
    {
        return $this->belongsTo(BranchUsers::class);
    }

    public function getBranchUsers()
    {
        return BranchUsers::on($this->connection_name)->get();
    }

    public function getBranchUsersCount()
    {
        return BranchUsers::on($this->connection_name)->count();
    }

    public function getActiveBranchUsersCount()
    {
        return BranchUsers::on($this->connection_name)->where('is_active', true)->count();
    }

}
