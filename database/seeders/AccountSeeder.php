<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Account};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customer1 = User::where('email', 'customer1@example.com')->first();
        $customer2 = User::where('email', 'customer2@example.com')->first();

        if ($customer1) {
            Account::updateOrCreate(
                ['user_id' => $customer1->id],
                [
                    'account_number' => 'ACC' . Str::random(10),
                    'balance' => 1500.75,
                    'currency' => 'AZN',
                    'card_number' => null,
                    'pin' => null,
                ]
            );
        }

        if ($customer2) {
            Account::updateOrCreate(
                ['user_id' => $customer2->id],
                [
                    'account_number' => 'ACC' . Str::random(10),
                    'balance' => 550.00,
                    'currency' => 'AZN',
                    'card_number' => null,
                    'pin' => null,
                ]
            );
        }

        Account::updateOrCreate(
            ['card_number' => '1111222233334444'],
            [
                'user_id' => null,
                'account_number' => 'ATM' . Str::random(10),
                'pin' => Hash::make('1234'),
                'balance' => 2500.00,
                'currency' => 'AZN',
            ]
        );

        Account::updateOrCreate(
            ['card_number' => '5555666677778888'],
            [
                'user_id' => null,
                'account_number' => 'ATM' . Str::random(10),
                'pin' => Hash::make('5678'),
                'balance' => 100.50,
                'currency' => 'AZN',
            ]
        );
    }
}
