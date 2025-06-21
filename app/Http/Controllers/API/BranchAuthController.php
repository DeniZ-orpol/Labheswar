<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchUsers;
use App\Models\Role;
use App\Models\User;
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

        // Find user in master database
        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->with(['branch', 'role_data']) // Load relationships
            ->first();

        // Validate user and password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Revoke all old tokens (optional for security)
        $user->tokens()->delete();

        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Create a new token
        $tokenResult = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $tokenResult,
                'user' => [
                    'id' => $user->id,
                    'branch_id' => $user->branch_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role_data->role_name ?? null,
                ],
                'branch' => [
                    'id' => $user->branch->id ?? null,
                    'name' => $user->branch->name ?? null,
                    'code' => $user->branch->code ?? null,
                    'connection' => $user->branch->connection_name ?? null,
                ]
            ]
        ]);
    }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|string',
    //     ]);

    //     $email = $request->email;
    //     $password = $request->password;
    //     $branches = Branch::where('status', 'active')->get();

    //     foreach ($branches as $branch) {
    //         try {
    //             $user = BranchUsers::forDatabase($branch->connection_name)
    //                 ->where('email', $email)
    //                 ->where('is_active', true)
    //                 ->first();

    //             if ($user && Hash::check($password, $user->password)) {
    //                 $user->update(['last_login_at' => now()]);
    //                 $user->setBranchInfo($branch);

    //                 $role = Role::forDatabase($branch->connection_name)->find($user->role_id);
    //                 // Debug: Log the user's connection
    //                 \Log::info('Creating token for user on connection: ' . $user->getConnectionName());

    //                 // Create token using custom service
    //                 // $tokenResult = $this->tokenService->createToken($user, 'API Token');
    //                 $tokenResult = $this->tokenService->createTokenInBranchDB($user, 'API Token', $branch->connection_name);

    //                 // Debug: Log token creation result
    //                 \Log::info('Token created successfully: ' . ($tokenResult ? 'Yes' : 'No'));
    //                 \Log::info('Plain text token length: ' . strlen($tokenResult->plainTextToken));
    //                 \Log::info('Plain text token preview: ' . substr($tokenResult->plainTextToken, 0, 10) . '...');

    //                 return response()->json([
    //                     'success' => true,
    //                     'message' => 'Login successful',
    //                     'data' => [
    //                         'token' => $tokenResult->plainTextToken,
    //                         'user' => [
    //                             'id' => $user->id,
    //                             'branch_id' => $branch->id,
    //                             'name' => $user->name,
    //                             'email' => $user->email,
    //                             'role' => $role ? $role->role_name : null,
    //                         ]
    //                         // 'branch' => [
    //                         //     'id' => $branch->id,
    //                         //     'name' => $branch->name,
    //                         //     'code' => $branch->code,
    //                         // ]
    //                     ]
    //                 ]);
    //             }
    //         } catch (Exception $e) {
    //             return response()->json([
    //                 'message' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Invalid credentials'
    //     ], 401);
    // }


    public function profile(Request $request)
    {
        $user = $request->user()->load(['branch', 'role_data']);

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'dob' => $user->dob,
                    'role' => $user->role_data->role_name,
                    'is_active' => $user->is_active,
                    'last_login_at' => $user->last_login_at,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ],
                'branch' => [
                    'id' => $user->branch->id,
                    'name' => $user->branch->name,
                    'code' => $user->branch->code,
                    'location' => $user->branch->location,
                    'connection' => $user->branch->connection_name,
                ]
            ]
        ]);
    }
    // public function profile(Request $request)
    // {
    //     try {
    //         $user = $request->authenticated_user;
    //         $branch = $request->authenticated_branch;

    //         if (!$user) {
    //             return response()->json(['error' => 'User not found'], 401);
    //         }

    //         $role = Role::find($user->role_id);

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'id' => $user->id,
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'mobile' => $user->mobile,
    //                 'role' => $role ? $role->role_name : null,
    //                 'branch' => [
    //                     'id' => $branch->id,
    //                     'name' => $branch->name,
    //                     'code' => $branch->code,
    //                 ]
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }


    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request)
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    // public function logout(Request $request)
    // {
    //     try {
    //         $token = $request->bearerToken();

    //         if ($this->tokenService->revokeToken($token)) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Logged out successfully'
    //             ]);
    //         }

    //         return response()->json(['error' => 'Token not found'], 404);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

}
