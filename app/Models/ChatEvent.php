<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_message_id',
        'user_id',
        'session_id',
        'type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}



