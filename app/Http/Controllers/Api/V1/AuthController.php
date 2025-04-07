<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\{LoginRequest, AtmLoginRequest};
use App\Http\Resources\Api\V1\{UserResource, AccountResource};
use App\Services\AuthService\AuthServiceInterface;
use Illuminate\Http\{JsonResponse, Request};
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Account;
use App\Exceptions\ApiAuthenticationException;
use App\Exceptions\OperationFailedException;
use App\Exceptions\ResourceNotFoundException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class AuthController extends Controller
{
    public function __construct(protected AuthServiceInterface $authService) {}

    public function loginUser(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        try {
            $user = $this->authService->authenticateUser($credentials['email'], $credentials['password']);
            if (!$user)
                throw new ApiAuthenticationException('Invalid credentials');

            $customClaims = ['typ' => 'user_session', 'uid' => $user->id, 'role' => $user->role];
            $token = JWTAuth::claims($customClaims)->fromUser($user);

            return $this->respondWithToken($token, new UserResource($user));
        } catch (JWTException $e) {
            throw new OperationFailedException('Could not create user token');
        }
    }

    public function loginAtm(AtmLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        try {
            $account = $this->authService->authenticateAtm($credentials['card_number'], $credentials['pin']);
            if (!$account)
                throw new ApiAuthenticationException("Invalid card number or PIN");

            $payloadFactory = JWTAuth::factory();

            $claims = [
                'sub' => $account->id,
                'typ' => 'atm_session',
                'aid' => $account->id,
                'pan' => substr($credentials['card_number'], -4)
            ];

            $payload = $payloadFactory->claims($claims)->make();
            $token = JWTAuth::encode($payload)->get();

            return $this->respondWithToken($token, new AccountResource($account));
        } catch (JWTException $e) {
            throw new OperationFailedException('Could not create ATM token');
        }
    }

    public function me(Request $request): UserResource | AccountResource
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $tokenType = $payload->get('typ');

        if ($tokenType === 'user_session') {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                throw new ResourceNotFoundException('User not found');
            }
            return new UserResource($user);
        } elseif ($tokenType === 'atm_session') {
            $accountId = $payload->get('aid');
            $account = Account::find($accountId);
            if (!$account) {
                throw new ResourceNotFoundException('Account not found');
            }
            return new AccountResource($account);
        }

        throw new ApiAuthenticationException('Unknown token type');
    }

    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            throw new OperationFailedException('Could not invalidate token: ' . $e->getMessage());
        }
    }

    protected function respondWithToken(string $token, $resource = null): JsonResponse
    {
        static $ttl = null;
        $ttl = $ttl ?? JWTAuth::factory()->getTTL();
        $response = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60,
        ];
        if ($resource) {
            $resourceKey = strtolower(class_basename($resource->resource));
            if ($resource instanceof JsonResource && !$resource->resource instanceof Collection)
                $response[$resourceKey] = $resource;
        }
        return response()->json($response);
    }
}
