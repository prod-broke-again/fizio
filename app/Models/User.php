<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('Admin') || $this->email === 'laravelka@yandex.ru';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? Storage::disk('public')->url($this->avatar) : null;
    }

    /**
     * Get the progress for the user.
     */
    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'fitness_goal',
        'activity_level',
        'device_token',
        'telegram_id',
        'telegram_username',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];

    /**
     * Проверяет, связан ли пользователь с Telegram
     *
     * @return bool
     */
    public function hasConnectedTelegram(): bool
    {
        return $this->telegram_id !== null;
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
