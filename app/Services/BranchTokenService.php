<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\BranchPersonalAccessToken;
use App\Models\BranchUsers;

class BranchTokenService
{
    /**
     * Create token for branch user
     */
    public function createToken(BranchUsers $user, $name, array $abilities = ['*'], $expiresAt = null)
    {
        $plainTextToken = BranchPersonalAccessToken::generateToken();
        $hashedToken = BranchPersonalAccessToken::hashToken($plainTextToken);

        $token = new BranchPersonalAccessToken();
        $token->setConnection($user->getConnectionName());
        
        $token->fill([
            'tokenable_type' => get_class($user),
            'tokenable_id' => $user->id,
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);
        
        $token->save();

        return (object) [
            'accessToken' => $token,
            'plainTextToken' => $plainTextToken,
        ];
    }

    /**
     * Find token across all branches
     */
    public function findToken($plainTextToken)
    {
        $hashedToken = BranchPersonalAccessToken::hashToken($plainTextToken);
        $branches = Branch::all();

        foreach ($branches as $branch) {
            try {
                $token = BranchPersonalAccessToken::on($branch->connection_name)
                    ->where('token', $hashedToken)
                    ->first();

                if ($token && !$token->isExpired()) {
                    // Find the user
                    $user = BranchUsers::on($branch->connection_name)->find($token->tokenable_id);
                    
                    if ($user && $user->is_active) {
                        return [
                            'token' => $token,
                            'user' => $user,
                            'branch' => $branch,
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Revoke token
     */
    public function revokeToken($plainTextToken)
    {
        $result = $this->findToken($plainTextToken);
        
        if ($result) {
            $result['token']->delete();
            return true;
        }
        
        return false;
    }

    /**
     * Get user tokens from specific branch
     */
    public function getUserTokens($userId, $branchConnection)
    {
        return BranchPersonalAccessToken::on($branchConnection)
            ->where('tokenable_id', $userId)
            ->where('tokenable_type', BranchUsers::class)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}