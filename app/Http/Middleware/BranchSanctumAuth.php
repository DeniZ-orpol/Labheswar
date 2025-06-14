<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\BranchUsers;
use App\Services\BranchTokenService;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class BranchSanctumAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $tokenService;

    public function __construct(BranchTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $result = $this->tokenService->findToken($token);

        if (!$result) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        $user = $result['user'];
        $branch = $result['branch'];
        $accessToken = $result['token'];

        // Update last used
        $accessToken->updateLastUsed();

        // Set user with access token
        $user->withAccessToken($accessToken);
        $user->setBranchInfo($branch);

        // Set in request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $request->merge([
            'authenticated_user' => $user,
            'authenticated_branch' => $branch,
            'access_token' => $accessToken,
        ]);

        return $next($request);
    }
}
