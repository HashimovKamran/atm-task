<?php

namespace App\Services\AuthService;

use App\Models\User;
use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Services\AuthService\AuthServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthManager implements AuthServiceInterface
{
    public function __construct(protected AccountRepositoryInterface $accountRepository) {}

    public function authenticateUser(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function authenticateAtm(string $cardNumber, string $pin): ?Account
    {
        $account = Account::where('card_number', $cardNumber)->first();

        if ($account)
            if (Hash::check($pin, $account->pin))
                return $account;
        return null;
    }

    public function logout(): void
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            throw new JWTException('Could not invalidate token: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getAuthenticatedUser(): ?User
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return $user instanceof User ? $user : null;
        } catch (JWTException $e) {
            Log::warning('Attempted to get authenticated user but failed after middleware.', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
