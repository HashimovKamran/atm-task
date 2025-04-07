<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Contracts\{AccountRepositoryInterface, BanknoteRepositoryInterface, TransactionRepositoryInterface};
use App\Repositories\Eloquent\{EloquentAccountRepository, EloquentBanknoteRepository, EloquentTransactionRepository};

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccountRepositoryInterface::class, EloquentAccountRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(BanknoteRepositoryInterface::class, EloquentBanknoteRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
