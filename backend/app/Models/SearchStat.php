<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchStat extends Model
{
    protected $fillable = [
        'payload',
        'calculated_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'calculated_at' => 'datetime',
    ];
}