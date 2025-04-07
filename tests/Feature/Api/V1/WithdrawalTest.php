<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\{Account, Banknote};
use Illuminate\Support\Facades\Hash;
use App\Enums\{TransactionType, TransactionStatus};
use Database\Seeders\BanknoteSeeder;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    protected Account $account;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BanknoteSeeder::class);

        $this->account = Account::factory()->create([
            'user_id' => null,
            'card_number' => '1111222233334444',
            'pin' => Hash::make('1234'),
            'balance' => 500.00,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login/atm', [
            'card_number' => '1111222233334444',
            'pin' => '1234',
        ]);
        $this->token = $loginResponse->json('access_token');
    }

    public function test_can_withdraw_valid_amount_successfully(): void
    {
        $initialBalance = $this->account->balance;
        $withdrawAmount = 125.00;

        $initial100s = Banknote::where('denomination', 100)->first()->count;
        $initial20s = Banknote::where('denomination', 20)->first()->count;
        $initial5s = Banknote::where('denomination', 5)->first()->count;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/withdrawals', [
            'amount' => $withdrawAmount,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'transaction_id',
                    'amount_withdrawn',
                    'dispensed_notes' => ['100', '20', '5'],
                    'new_balance',
                    'timestamp',
                ]
            ])
            ->assertJsonPath('data.amount_withdrawn', $withdrawAmount)
            ->assertJsonPath('data.dispensed_notes.100', 1)
            ->assertJsonPath('data.dispensed_notes.20', 1)
            ->assertJsonPath('data.dispensed_notes.5', 1)
            ->assertJsonPath('data.new_balance', $initialBalance - $withdrawAmount);

        $this->assertDatabaseHas('accounts', [
            'id' => $this->account->id,
            'balance' => $initialBalance - $withdrawAmount,
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->account->id,
            'type' => TransactionType::Withdrawal->value,
            'amount' => $withdrawAmount,
            'status' => TransactionStatus::Completed->value,
            'balance_after' => $initialBalance - $withdrawAmount,
        ]);

        $this->assertDatabaseHas('banknotes', [
            'denomination' => 100,
            'count' => $initial100s - 1,
        ]);
        $this->assertDatabaseHas('banknotes', [
            'denomination' => 20,
            'count' => $initial20s - 1,
        ]);
        $this->assertDatabaseHas('banknotes', [
            'denomination' => 5,
            'count' => $initial5s - 1,
        ]);
    }

    public function test_cannot_withdraw_with_insufficient_funds(): void
    {
        $initialBalance = 50.00;
        $this->account->update(['balance' => $initialBalance]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/withdrawals', [
            'amount' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Insufficient funds available in the account.']);

        $this->assertDatabaseHas('accounts', [
            'id' => $this->account->id,
            'balance' => $initialBalance,
        ]);
    }

    public function test_cannot_withdraw_amount_not_multiple_of_smallest_unit(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/withdrawals', [
            'amount' => 126.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }
}
