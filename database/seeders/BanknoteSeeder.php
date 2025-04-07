<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banknote;

class BanknoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $denominations = [
            200 => [50, true],
            100 => [100, true],
            50 => [200, true],
            20 => [250, true],
            10 => [300, true],
            5 => [400, true],
        ];

        $currency = config('atm.currency', 'AZN');

        foreach ($denominations as $value => $details) {
            Banknote::updateOrCreate(
                [
                    'denomination' => $value,
                    'currency' => $currency,
                ],
                [
                    'count' => $details[0],
                    'is_available' => $details[1],
                ]
            );
        }
    }
}
