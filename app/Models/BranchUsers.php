<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchUsers extends Model
{
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
        'password_changed_at'
    ];
}
