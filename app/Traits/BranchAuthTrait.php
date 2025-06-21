<?php

namespace App\Traits;

use App\Models\Branch;

trait BranchAuthTrait
{
    protected function authenticateAndConfigureBranch()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        if ($user->is_active == '0') {
            return response()->json([
                'success' => false,
                'message' => 'User account is not active'
            ], 403);
        }

        $branch = Branch::where('id', $user->branch_id)
            ->where('status', 'active')
            ->first();

        if (!$branch) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'No accessible branch found for user'
            ]);
        }

        configureBranchConnection($branch);

        return [
            'user' => $user,
            'branch' => $branch
        ];
    }
}
