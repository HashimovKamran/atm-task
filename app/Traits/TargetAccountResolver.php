<?php

namespace App\Traits;

use App\Exceptions\ApiAuthenticationException;
use App\Services\AccountService\AccountServiceInterface;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

trait TargetAccountResolver
{
    protected function getTargetAccountId(Request $request, AccountServiceInterface $accountService): int
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $tokenType = $payload->get('typ');

        if ($tokenType === 'atm_session') {
            $accountId = $payload->get('aid');
            if (!$accountId) {
                throw new ApiAuthenticationException('ATM token payload is missing account ID.');
            }
            return (int) $accountId;
        } elseif ($tokenType === 'user_session') {
            $user = $request->user('api');
            if (!$user) {
                throw new ApiAuthenticationException('User session token is invalid.');
            }
            $account = $accountService->getAccountByUser($user);
            return $account->id;
        }

        throw new ApiAuthenticationException('Unknown token type.');
    }
}