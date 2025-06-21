<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role',
        'dob',
        'role_id',
        'permission',
        'is_active',
        'last_login_at',
        'password_changed_at',
        'branch_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date'
        ];
    }

    public function branch()  {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // public function role()
    // {
    //     return $this->belongsTo(Role::class, 'role_id');
    // }

    public function isSuperAdmin()
    {
        return $this->role === 'Superadmin';
    }

    public function managedBranches()
    {
        return $this->hasMany(Branch::class, 'branch_admin');
    }

    public function role_data()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
}
