<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'message',
        'is_user',
    ];

    protected $casts = [
        'is_user' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedTimeAttribute()
    {
        return $this->created_at->format('H:i');
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }
}
