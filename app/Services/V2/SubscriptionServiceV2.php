<?php

declare(strict_types=1);

namespace App\Services\V2;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Models\User;
use App\Models\UserSubscriptionV2;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionServiceV2
{
    /**
     * Получить активную подписку пользователя
     */
    public function getActiveSubscription(int $userId): ?UserSubscriptionV2
    {
        return UserSubscriptionV2::where('user_id', $userId)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->where('expires_at', '>', now())
            ->first();
    }
    
    /**
     * Создать новую подписку
     */
    public function createSubscription(int $userId, array $data): UserSubscriptionV2
    {
        // Отменяем предыдущие активные подписки
        $this->cancelActiveSubscriptions($userId);
        
        $startsAt = now();
        $expiresAt = $this->calculateExpirationDate($startsAt, $data['subscription_type']);
        
        return UserSubscriptionV2::create([
            'user_id' => $userId,
            'subscription_type' => $data['subscription_type'],
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);
    }
    
    /**
     * Отменить подписку пользователя
     */
    public function cancelSubscription(int $userId): bool
    {
        $subscription = $this->getActiveSubscription($userId);
        
        if (!$subscription) {
            return false;
        }
        
        $subscription->update([
            'status' => SubscriptionStatus::CANCELLED,
            'expires_at' => now(),
        ]);
        
        return true;
    }
    
    /**
     * Получить статус подписки пользователя
     */
    public function getSubscriptionStatus(int $userId): array
    {
        $subscription = $this->getActiveSubscription($userId);
        
        if (!$subscription) {
            return [
                'has_active_subscription' => false,
                'subscription_type' => null,
                'expires_at' => null,
                'days_remaining' => 0,
                'is_expired' => true,
            ];
        }
        
        $daysRemaining = now()->diffInDays($subscription->expires_at, false);
        
        return [
            'has_active_subscription' => true,
            'subscription_type' => $subscription->subscription_type,
            'expires_at' => $subscription->expires_at,
            'days_remaining' => max(0, $days_remaining),
            'is_expired' => $daysRemaining <= 0,
        ];
    }
    
    /**
     * Проверить, истекла ли подписка
     */
    public function isSubscriptionExpired(int $userId): bool
    {
        $subscription = $this->getActiveSubscription($userId);
        
        return !$subscription || $subscription->expires_at <= now();
    }
    
    /**
     * Получить все подписки пользователя
     */
    public function getUserSubscriptions(int $userId): Collection
    {
        return UserSubscriptionV2::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Обновить статусы истекших подписок
     */
    public function updateExpiredSubscriptions(): int
    {
        return UserSubscriptionV2::where('status', SubscriptionStatus::ACTIVE)
            ->where('expires_at', '<=', now())
            ->update(['status' => SubscriptionStatus::EXPIRED]);
    }
    
    /**
     * Рассчитать дату истечения подписки
     */
    private function calculateExpirationDate(Carbon $startsAt, SubscriptionType $type): Carbon
    {
        return match ($type) {
            SubscriptionType::MONTHLY => $startsAt->copy()->addMonth(),
            SubscriptionType::YEARLY => $startsAt->copy()->addYear(),
            SubscriptionType::LIFETIME => $startsAt->copy()->addYears(100), // Условно "навсегда"
        };
    }
    
    /**
     * Отменить все активные подписки пользователя
     */
    private function cancelActiveSubscriptions(int $userId): void
    {
        UserSubscriptionV2::where('user_id', $userId)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->update(['status' => SubscriptionStatus::CANCELLED]);
    }
}
