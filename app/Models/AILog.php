<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AILog extends Model
{
    protected $fillable = [
        'filename', 'model_used', 'sentiment', 'is_success', 'error_message', 'full_response'
    ];

    protected $casts = [
        'full_response' => 'array',
        'is_success' => 'boolean',
    ];
}
