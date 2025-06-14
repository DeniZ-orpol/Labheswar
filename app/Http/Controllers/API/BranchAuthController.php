<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchUsers;
use App\Models\Role;
use App\Services\BranchTokenService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BranchAuthController extends Controller
{
    protected $tokenService;

    public function __construct(BranchTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Simple login for branch users
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $password = $request->password;
        $branches = Branch::where('status', 'active')->get();

        foreach ($branches as $branch) {
            try {
                $user = BranchUsers::on($branch->connection_name)
                    ->where('email', $email)
                    ->where('is_active', true)
                    ->first();

                if ($user && Hash::check($password, $user->password)) {
                    $user->update(['last_login_at' => now()]);
                    $user->setBranchInfo($branch);

                    $role = Role::on($branch->connection_name)->find($user->role_id);

                    // Create token using custom service
                    $tokenResult = $this->tokenService->createToken($user, 'API Token');

                    return response()->json([
                        'success' => true,
                        'message' => 'Login successful',
                        'data' => [
                            'token' => $tokenResult->plainTextToken,
                            'user' => [
                                'id' => $user->id,
                                'branch_id' => $branch->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'role' => $role ? $role->role_name : null,
                            ]
                            // 'branch' => [
                            //     'id' => $branch->id,
                            //     'name' => $branch->name,
                            //     'code' => $branch->code,
                            // ]
                        ]
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'message' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function profile(Request $request)
    {
        try {
            $user = $request->authenticated_user;
            $branch = $request->authenticated_branch;

            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $role = Role::on($branch->connection_name)->find($user->role_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $role ? $role->role_name : null,
                    'branch' => [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'code' => $branch->code,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if ($this->tokenService->revokeToken($token)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully'
                ]);
            }

            return response()->json(['error' => 'Token not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
