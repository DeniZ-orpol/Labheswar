<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class BranchUsers extends Authenticatable
{
    use HasApiTokens, Notifiable, HasDynamicTable;

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
        'remember_token',
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

    // public function __construct(array $attributes = [])
    // {
    //     parent::__construct($attributes);
        
    //     // Set connection based on current branch context
    //     if (session()->has('current_branch_connection')) {
    //         $this->connection = session('current_branch_connection');
    //     }
    // }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
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
    // public function setConnection($name)
    // {
    //     $this->connection = $name;
    //     return $this;
    // }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    public function setBranchInfo($branch)
    {
        $this->branch_info = $branch;
        return $this;
    }
    
    /**
     * Create token using custom service
     */
    public function createToken($name, array $abilities = ['*'], $expiresAt = null)
    {
        $tokenService = app(\App\Services\BranchTokenService::class);
        return $tokenService->createToken($this, $name, $abilities, $expiresAt);
    }

    /**
     * Get user tokens
     */
    public function tokens()
    {
        $tokenService = app(\App\Services\BranchTokenService::class);
        return $tokenService->getUserTokens($this->id, $this->getTable());
    }

    /**
     * Set current access token
     */
    public function withAccessToken($token)
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * Get current access token
     */
    public function currentAccessToken()
    {
        return $this->accessToken ?? null;
    }

}
