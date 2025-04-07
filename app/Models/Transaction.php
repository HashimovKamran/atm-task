<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\{TransactionType, TransactionStatus};
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'dispensed_notes' => 'array',
        'transaction_time' => 'datetime',
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
    ];

    protected $dates = ['deleted_at'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
