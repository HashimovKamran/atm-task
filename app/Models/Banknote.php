<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banknote extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    
    protected $fillable = ['denomination', 'currency', 'is_available', 'count'];
    
    protected $casts = [
        'denomination' => 'integer',
        'is_available' => 'boolean',
        'count' => 'integer',
    ];
}