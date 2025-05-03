<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'message',
        'response',
        'is_processing'
    ];

    /**
     * Получить пользователя, которому принадлежит сообщение.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 