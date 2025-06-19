<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    // protected $connection = 'master';
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

    /**
     * Get the database name for this branch
     */
    public function getDatabaseName()
    {
        // Use the database_name field from your branches table
        return $this->connection_name;
    }

    public function user()
    {
        return $this->belongsTo(BranchUsers::class);
    }

    public function getBranchUsers()
    {
        return BranchUsers::forDatabase($this->getDatabaseName())->get();
    }

    public function getBranchUsersCount()
    {
        return BranchUsers::forDatabase($this->getDatabaseName())->count();
    }

    public function getActiveBranchUsersCount()
    {
        return BranchUsers::forDatabase($this->getDatabaseName())
            ->where('is_active', true)
            ->count();
    }

}
