<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasDynamicTable;
    protected $fillable = [
        'role_name',
    ];

    /**
     * Get the users associated with the role.
     */
    public function users()
    {
        return $this->hasMany(BranchUsers::class);
    }
    
    public function branchUsers()
    {
        return $this->hasMany(User::class);
    }
}
