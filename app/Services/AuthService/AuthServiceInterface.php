<?php

namespace App\Services\AuthService;

use App\Models\{User, Account};

interface AuthServiceInterface
{
    public function authenticateUser(string $email, string $password): ?User;

    public function authenticateAtm(string $cardNumber, string $pin): ?Account;

    public function logout(): void;

    public function getAuthenticatedUser(): ?User;
}
