<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'title', 'message', 'type', 'read', 'action', 'action_url'
    ];

    protected $casts = [
        'read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->setTimezone('Europe/Moscow');
    }

    protected function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->setTimezone('Europe/Moscow');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 