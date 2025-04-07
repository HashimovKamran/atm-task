<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{AuthController, AccountController, WithdrawalController};
use App\Http\Controllers\Api\V1\Admin\{AccountController as AdminAccountController, TransactionController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('login', 'loginUser');
        Route::post('login/atm', 'loginAtm');
        Route::post('logout', 'logout')->middleware('jwt.auth');
        Route::get('me', 'me')->middleware('jwt.auth');
    });

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/accounts/me', [AccountController::class, 'show']);
        Route::get('/accounts/me/transactions', [AccountController::class, 'transactions']);
        Route::post('/accounts/me/withdrawals', [WithdrawalController::class, 'store']);
    });

    Route::prefix('admin')->middleware(['jwt.auth', 'role:admin'])->group(function () {
        Route::post('/accounts', [AdminAccountController::class, 'store']);
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);
    });
});
