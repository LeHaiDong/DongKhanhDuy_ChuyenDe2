<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'state',
        'last_intent',
        'last_confidence',
        'last_message_at',
        'total_messages',
        'total_user_messages',
        'total_ai_messages',
        'handoff_requested',
        'contact_collected',
    ];

    protected $casts = [
        'state' => 'array',
        'last_message_at' => 'datetime',
        'handoff_requested' => 'boolean',
        'contact_collected' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'session_id', 'session_id');
    }
}



