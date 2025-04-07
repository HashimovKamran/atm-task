<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\{TransactionStatus, TransactionType};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\{User, Transaction};

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Transaction $transaction;
    protected string $adminToken;
    protected string $userToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminUser = User::where('role', 'admin')->first();
        $this->regularUser = User::where('role', 'customer')->first();

        $adminLogin = $this->postJson('/api/v1/auth/login', [
            'email' => $this->adminUser->email,
            'password' => 'password',
        ]);
        $this->adminToken = $adminLogin->json('access_token');

        $userLogin = $this->postJson('/api/v1/auth/login', [
            'email' => $this->regularUser->email,
            'password' => 'password',
        ]);
        $this->userToken = $userLogin->json('access_token');

        $account = $this->regularUser->account;
        $this->transaction = Transaction::factory()->create([
            'account_id' => $account->id,
            'amount' => 50,
            'type' => TransactionType::Withdrawal,
            'status' => TransactionStatus::Completed,
        ]);
    }

    public function test_admin_can_soft_delete_transaction(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
        ])->deleteJson('/api/v1/admin/transactions/' . $this->transaction->id);

        $response->assertStatus(204);

        $this->assertSoftDeleted('transactions', [
            'id' => $this->transaction->id,
        ]);
    }

    public function test_non_admin_cannot_delete_transaction(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken,
            'Accept' => 'application/json',
        ])->deleteJson('/api/v1/admin/transactions/' . $this->transaction->id);

        $response->assertStatus(403)
            ->assertJson(['message' => 'You do not have the required role(s) to access this resource.']);

        $this->assertNotSoftDeleted('transactions', [
            'id' => $this->transaction->id,
        ]);
    }

    public function test_delete_non_existent_transaction_returns_404(): void
    {
        $nonExistentId = 9999;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
        ])->deleteJson('/api/v1/admin/transactions/' . $nonExistentId);

        $response->assertStatus(404);
    }
}
