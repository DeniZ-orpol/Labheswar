<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchUsers extends Model
{
    protected $connection = null;
    protected $table = 'branch_users';
    protected $fillable = [
        'name',
        'dob',
        'email',
        'email_verified_at',
        'password',
        'mobile',
        'role',
        'permissions',
        'is_active',
        'last_login_at',
        'password_changed_at',
        'role_id'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'dob' => 'date',
        'last_login_at' => 'datetime'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Set connection based on current branch context
        if (session()->has('current_branch_connection')) {
            $this->connection = session('current_branch_connection');
        }
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function setConnection($name)
    {
        $this->connection = $name;
        return $this;
    }

    public function isBranchAdmin()
    {
        return $this->role === 'admin';
    }

    public function hasPermission($permission)
    {
        return is_array($this->permissions) && 
               isset($this->permissions[$permission]) && 
               $this->permissions[$permission];
    }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }


}
