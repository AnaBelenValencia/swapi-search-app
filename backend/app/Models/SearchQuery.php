<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    protected $fillable = [
        'resource',
        'term',
        'page',
        'limit',
        'results_count',
        'response_time_ms',
        'searched_at',
    ];

    protected $casts = [
        'searched_at'       => 'datetime',
        'response_time_ms'  => 'float',
    ];
}
