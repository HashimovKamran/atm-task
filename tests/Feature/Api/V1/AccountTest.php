<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Account $account;
    protected string $userToken;

    protected User $adminUser;
    protected string $adminToken;

    protected Account $atmAccount;
    protected string $atmToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'customer1@example.com')->firstOrFail();
        $this->account = $this->user->account()->firstOrFail();

        $userLogin = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $this->userToken = $userLogin->json('access_token');

        Transaction::factory(5)->create([
            'account_id' => $this->account->id,
            'type' => \App\Enums\TransactionType::Withdrawal,
            'status' => \App\Enums\TransactionStatus::Completed,
        ]);
        Transaction::factory(3)->create([
            'account_id' => $this->account->id,
            'type' => \App\Enums\TransactionType::FailedWithdrawal,
            'status' => \App\Enums\TransactionStatus::Failed,
        ]);

        $this->adminUser = User::where('role', 'admin')->firstOrFail();
        $adminLogin = $this->postJson('/api/v1/auth/login', [
            'email' => $this->adminUser->email,
            'password' => 'password',
        ]);
        $this->adminToken = $adminLogin->json('access_token');

        $this->atmAccount = Account::where('card_number', '1111222233334444')->firstOrFail();
        $atmLogin = $this->postJson('/api/v1/auth/login/atm', [
            'card_number' => $this->atmAccount->card_number,
            'pin' => '1234',
        ]);
        $this->atmToken = $atmLogin->json('access_token');
    }

    public function test_authenticated_api_user_can_get_their_account_details(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/accounts/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'account_number',
                    'balance',
                    'currency',
                    'user_id',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJsonPath('data.id', $this->account->id)
            ->assertJsonPath('data.user_id', $this->user->id)
            ->assertJsonPath('data.account_number', $this->account->account_number);
    }

    public function test_authenticated_atm_session_can_get_its_account_details(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->atmToken,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/accounts/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'account_number',
                    'balance',
                    'currency',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJsonPath('data.id', $this->atmAccount->id)
            ->assertJsonPath('data.user_id', null)
            ->assertJsonPath('data.account_number', $this->atmAccount->account_number);
    }

    public function test_unauthenticated_user_cannot_get_account_details(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson('/api/v1/accounts/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_their_transaction_history_paginated(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/accounts/me/transactions?size=5&index=0');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'index',
                'size',
                'count',
                'pages',
                'from',
                'hasPrevious',
                'hasNext',
                'items' => [
                    '*' => [
                        'id',
                        'type',
                        'type_label',
                        'amount',
                        'balance_before',
                        'balance_after',
                        'status',
                        'status_label',
                        'dispensed_notes',
                        'failure_reason',
                        'transaction_time',
                    ]
                ]
            ])
            ->assertJsonCount(5, 'items')
            ->assertJsonPath('count', 8)
            ->assertJsonPath('pages', 2)
            ->assertJsonPath('index', 0)
            ->assertJsonPath('size', 5)
            ->assertJsonPath('hasPrevious', false)
            ->assertJsonPath('hasNext', true);
    }

    public function test_transaction_history_can_be_filtered_by_type(): void
    {
        $failedType = \App\Enums\TransactionType::FailedWithdrawal->value;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/accounts/me/transactions?type=' . $failedType);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'items')
            ->assertJsonPath('count', 3);

        $response->assertJsonPath('items.0.type', $failedType);
    }

    public function test_transaction_history_pagination_works_correctly_for_next_page(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/accounts/me/transactions?size=5&index=1');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'items')
            ->assertJsonPath('count', 8)
            ->assertJsonPath('pages', 2)
            ->assertJsonPath('index', 1)
            ->assertJsonPath('size', 5)
            ->assertJsonPath('hasPrevious', true)
            ->assertJsonPath('hasNext', false);
    }

    public function test_unauthenticated_user_cannot_get_transaction_history(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson('/api/v1/accounts/me/transactions');

        $response->assertStatus(401);
    }
}
