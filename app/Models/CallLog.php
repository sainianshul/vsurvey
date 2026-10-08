<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    protected $fillable = [
        'provider_call_id', 'user_id', 'phone_number', 'name', 'call_timing', 'call_duration', 
        'audio_text', 'sentiment', 'ai_response', 'model_used', 'is_success'
    ];

    protected $casts = [
        'ai_response' => 'array',
        'is_success' => 'boolean',
        'call_timing' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
