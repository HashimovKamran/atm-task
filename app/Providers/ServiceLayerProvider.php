<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\AccountService\{AccountServiceInterface, AccountManager};
use App\Services\AuthService\{AuthServiceInterface, AuthManager};
use App\Services\TransactionService\{TransactionServiceInterface, TransactionManager};
use App\Services\WithdrawalService\{WithdrawalServiceInterface, WithdrawalManager};

class ServiceLayerProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthManager::class);
        $this->app->bind(AccountServiceInterface::class, AccountManager::class);
        $this->app->bind(WithdrawalServiceInterface::class, WithdrawalManager::class);
        $this->app->bind(TransactionServiceInterface::class, TransactionManager::class);
    }
    
    public function boot(): void
    {
        //
    }
}
