<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'account_number', 'balance', 'currency'];
    protected $casts = ['balance' => 'decimal:2'];

    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}