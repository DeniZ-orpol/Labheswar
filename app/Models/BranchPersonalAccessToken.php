<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BranchPersonalAccessToken extends Model
{
    protected $connection = null;
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Set the database connection for this model
     */
    public function setConnection($name)
    {
        $this->connection = $name;
        return $this;
    }

    /**
     * Generate a new token
     */
    public static function generateToken()
    {
        return Str::random(40);
    }

    /**
     * Hash a token
     */
    public static function hashToken($token)
    {
        return hash('sha256', $token);
    }

    /**
     * Check if token is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed()
    {
        $this->forceFill(['last_used_at' => now()])->save();
    }
}
