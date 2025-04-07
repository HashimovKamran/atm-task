<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\{User, Account};
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected Account $testAtmAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer'
        ]);

        $this->testAtmAccount = Account::factory()->create([
            'user_id' => null,
            'card_number' => '1234123412341234',
            'pin' => Hash::make('1122'),
            'balance' => 500.00
        ]);
    }


    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role'
                ]
            ])
            ->assertJsonPath('token_type', 'bearer')
            ->assertJsonPath('user.email', 'testuser@example.com');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_atm_can_login_with_valid_card_pin(): void
    {
        $response = $this->postJson('/api/v1/auth/login/atm', [
            'card_number' => '1234123412341234',
            'pin' => '1122',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'account' => [
                    'id',
                    'account_number',
                    'balance',
                    'currency'
                ]
            ])
            ->assertJsonPath('token_type', 'bearer')
            ->assertJsonPath('account.id', $this->testAtmAccount->id);
    }

    public function test_atm_cannot_login_with_invalid_pin(): void
    {
        $response = $this->postJson('/api/v1/auth/login/atm', [
            'card_number' => '1234123412341234',
            'pin' => '9999',
        ]);

        $response->assertStatus(401)->assertJson(['message' => 'Invalid card number or PIN']);
    }


    public function test_authenticated_user_can_get_profile(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'password',
        ]);
        $token = $loginResponse->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role']])
            ->assertJsonPath('data.email', 'testuser@example.com');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'testuser@example.com',
            'password' => 'password',
        ]);
        $token = $loginResponse->json('access_token');

        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);

        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/auth/me');

        $profileResponse->assertStatus(401);
    }
}
