<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель подписки пользователя V2
 */
class UserSubscriptionV2 extends BaseModel
{
    /**
     * Название таблицы
     */
    protected $table = 'user_subscriptions_v2';

    /**
     * Атрибуты, которые можно массово назначать
     */
    protected $fillable = [
        'user_id',
        'subscription_type',
        'status',
        'starts_at',
        'expires_at',
    ];

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     */
    protected $casts = [
        'subscription_type' => SubscriptionType::class,
        'status' => SubscriptionStatus::class,
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Отношение к пользователю
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope для активных подписок
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::ACTIVE);
    }

    /**
     * Scope для истекших подписок
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::EXPIRED);
    }

    /**
     * Scope для отмененных подписок
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::CANCELLED);
    }

    /**
     * Scope для подписок по типу
     */
    public function scopeByType(Builder $query, SubscriptionType $type): Builder
    {
        return $query->where('subscription_type', $type);
    }

    /**
     * Проверить, активна ли подписка
     */
    public function isActive(): bool
    {
        if ($this->status !== SubscriptionStatus::ACTIVE) {
            return false;
        }

        // Для пожизненной подписки не проверяем дату
        if ($this->subscription_type === SubscriptionType::LIFETIME) {
            return true;
        }

        // Проверяем, не истекла ли подписка
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * Проверить, истекла ли подписка
     */
    public function isExpired(): bool
    {
        if ($this->subscription_type === SubscriptionType::LIFETIME) {
            return false;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Проверить, отменена ли подписка
     */
    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::CANCELLED;
    }

    /**
     * Активировать подписку
     */
    public function activate(): void
    {
        $this->update(['status' => SubscriptionStatus::ACTIVE]);
    }

    /**
     * Отменить подписку
     */
    public function cancel(): void
    {
        $this->update(['status' => SubscriptionStatus::CANCELLED]);
    }

    /**
     * Пометить подписку как истекшую
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => SubscriptionStatus::EXPIRED]);
    }

    /**
     * Получить оставшееся время подписки в днях
     */
    public function getRemainingDays(): ?int
    {
        if ($this->subscription_type === SubscriptionType::LIFETIME) {
            return null;
        }

        if ($this->expires_at === null) {
            return null;
        }

        $now = now();
        if ($this->expires_at->isPast()) {
            return 0;
        }

        return $this->expires_at->diffInDays($now);
    }

    /**
     * Получить оставшееся время подписки в часах
     */
    public function getRemainingHours(): ?int
    {
        if ($this->subscription_type === SubscriptionType::LIFETIME) {
            return null;
        }

        if ($this->expires_at === null) {
            return null;
        }

        $now = now();
        if ($this->expires_at->isPast()) {
            return 0;
        }

        return $this->expires_at->diffInHours($now);
    }

    /**
     * Получить прогресс подписки в процентах
     */
    public function getProgressPercentage(): ?float
    {
        if ($this->subscription_type === SubscriptionType::LIFETIME) {
            return null;
        }

        if ($this->expires_at === null || $this->starts_at === null) {
            return null;
        }

        $totalDuration = $this->starts_at->diffInSeconds($this->expires_at);
        $elapsedDuration = $this->starts_at->diffInSeconds(now());

        if ($totalDuration <= 0) {
            return 100.0;
        }

        $progress = ($elapsedDuration / $totalDuration) * 100;
        return min(100.0, max(0.0, $progress));
    }

    /**
     * Получить название типа подписки
     */
    public function getTypeLabel(): string
    {
        return $this->subscription_type->label();
    }

    /**
     * Получить название статуса подписки
     */
    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    /**
     * Получить email пользователя
     */
    public function getUserEmail(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Получить имя пользователя
     */
    public function getUserName(): ?string
    {
        return $this->user?->name;
    }
}
