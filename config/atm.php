<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency code used for transactions and displaying balances.
    | Ensure this matches the currency used in your database (e.g., accounts, banknotes).
    |
    */

    'currency' => env('ATM_CURRENCY', 'AZN'), // .env faylından almaq daha yaxşıdır

    /*
    |--------------------------------------------------------------------------
    | Banknote Denominations
    |--------------------------------------------------------------------------
    |
    | Define the available banknote denominations. The order in this array is
    | crucial as it determines the preference for dispensing cash (usually
    | highest to lowest). This array is used by the WithdrawalService.
    | The actual availability and count are managed in the 'banknotes' table.
    |
    */

    'denominations_preference' => [200, 100, 50, 20, 10, 5],

    /*
    |--------------------------------------------------------------------------
    | Withdrawal Limits
    |--------------------------------------------------------------------------
    |
    | Define the minimum and maximum withdrawal amounts allowed per transaction.
    | These values are used for validation.
    |
    */

    'min_withdrawal' => env('ATM_MIN_WITHDRAWAL', 5),
    'max_withdrawal_per_tx' => env('ATM_MAX_WITHDRAWAL_PER_TX', 1000),

    /*
    |--------------------------------------------------------------------------
    | Smallest Dispensable Unit
    |--------------------------------------------------------------------------
    |
    | This usually corresponds to the smallest available banknote denomination.
    | It's used in validation rules to ensure the requested amount is a multiple
    | of this unit, making it possible to dispense exactly.
    |
    */

    'smallest_dispensable_unit' => env('ATM_SMALLEST_UNIT', 5),
];