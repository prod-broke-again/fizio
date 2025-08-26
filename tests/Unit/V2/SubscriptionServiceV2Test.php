<?php

declare(strict_types=1);

namespace Tests\Unit\V2;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Models\User;
use App\Models\UserSubscriptionV2;
use App\Services\V2\SubscriptionServiceV2;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceV2Test extends TestCase
{
    use RefreshDatabase;

    private SubscriptionServiceV2 $subscriptionService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->subscriptionService = new SubscriptionServiceV2();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_get_active_subscription(): void
    {
        $subscription = UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addMonth(),
        ]);
        
        $activeSubscription = $this->subscriptionService->getActiveSubscription($this->user->id);
        
        $this->assertNotNull($activeSubscription);
        $this->assertEquals($subscription->id, $activeSubscription->id);
        $this->assertEquals(SubscriptionStatus::ACTIVE, $activeSubscription->status);
    }

    /** @test */
    public function it_returns_null_for_expired_subscription(): void
    {
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->subDay(),
        ]);
        
        $activeSubscription = $this->subscriptionService->getActiveSubscription($this->user->id);
        
        $this->assertNull($activeSubscription);
    }

    /** @test */
    public function it_returns_null_for_cancelled_subscription(): void
    {
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::CANCELLED,
            'expires_at' => now()->addMonth(),
        ]);
        
        $activeSubscription = $this->subscriptionService->getActiveSubscription($this->user->id);
        
        $this->assertNull($activeSubscription);
    }

    /** @test */
    public function it_can_create_monthly_subscription(): void
    {
        $subscriptionData = [
            'subscription_type' => SubscriptionType::MONTHLY,
        ];
        
        $subscription = $this->subscriptionService->createSubscription($this->user->id, $subscriptionData);
        
        $this->assertNotNull($subscription);
        $this->assertEquals($this->user->id, $subscription->user_id);
        $this->assertEquals(SubscriptionType::MONTHLY, $subscription->subscription_type);
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertEquals(
            now()->addMonth()->format('Y-m-d'),
            $subscription->expires_at->format('Y-m-d')
        );
    }

    /** @test */
    public function it_can_create_yearly_subscription(): void
    {
        $subscriptionData = [
            'subscription_type' => SubscriptionType::YEARLY,
        ];
        
        $subscription = $this->subscriptionService->createSubscription($this->user->id, $subscriptionData);
        
        $this->assertEquals(SubscriptionType::YEARLY, $subscription->subscription_type);
        $this->assertEquals(
            now()->addYear()->format('Y-m-d'),
            $subscription->expires_at->format('Y-m-d')
        );
    }

    /** @test */
    public function it_can_create_lifetime_subscription(): void
    {
        $subscriptionData = [
            'subscription_type' => SubscriptionType::LIFETIME,
        ];
        
        $subscription = $this->subscriptionService->createSubscription($this->user->id, $subscriptionData);
        
        $this->assertEquals(SubscriptionType::LIFETIME, $subscription->subscription_type);
        $this->assertTrue($subscription->expires_at->isAfter(now()->addYears(50)));
    }

    /** @test */
    public function it_cancels_previous_subscriptions_when_creating_new(): void
    {
        // Создаем первую подписку
        $firstSubscription = UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addMonth(),
        ]);
        
        // Создаем вторую подписку
        $this->subscriptionService->createSubscription($this->user->id, [
            'subscription_type' => SubscriptionType::YEARLY,
        ]);
        
        // Проверяем, что первая подписка отменена
        $this->assertDatabaseHas('user_subscriptions_v2', [
            'id' => $firstSubscription->id,
            'status' => SubscriptionStatus::CANCELLED,
        ]);
    }

    /** @test */
    public function it_can_cancel_subscription(): void
    {
        $subscription = UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addMonth(),
        ]);
        
        $result = $this->subscriptionService->cancelSubscription($this->user->id);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('user_subscriptions_v2', [
            'id' => $subscription->id,
            'status' => SubscriptionStatus::CANCELLED,
        ]);
    }

    /** @test */
    public function it_returns_false_when_cancelling_nonexistent_subscription(): void
    {
        $result = $this->subscriptionService->cancelSubscription($this->user->id);
        
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_get_subscription_status(): void
    {
        $subscription = UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addDays(15),
        ]);
        
        $status = $this->subscriptionService->getSubscriptionStatus($this->user->id);
        
        $this->assertTrue($status['has_active_subscription']);
        $this->assertEquals($subscription->subscription_type, $status['subscription_type']);
        $this->assertEquals(15, $status['days_remaining']);
        $this->assertFalse($status['is_expired']);
    }

    /** @test */
    public function it_returns_correct_status_for_no_subscription(): void
    {
        $status = $this->subscriptionService->getSubscriptionStatus($this->user->id);
        
        $this->assertFalse($status['has_active_subscription']);
        $this->assertNull($status['subscription_type']);
        $this->assertEquals(0, $status['days_remaining']);
        $this->assertTrue($status['is_expired']);
    }

    /** @test */
    public function it_can_check_if_subscription_is_expired(): void
    {
        // Без подписки
        $isExpired = $this->subscriptionService->isSubscriptionExpired($this->user->id);
        $this->assertTrue($isExpired);
        
        // С активной подпиской
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addMonth(),
        ]);
        
        $isExpired = $this->subscriptionService->isSubscriptionExpired($this->user->id);
        $this->assertFalse($isExpired);
        
        // С истекшей подпиской
        UserSubscriptionV2::where('user_id', $this->user->id)->delete();
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->subDay(),
        ]);
        
        $isExpired = $this->subscriptionService->isSubscriptionExpired($this->user->id);
        $this->assertTrue($isExpired);
    }

    /** @test */
    public function it_can_get_user_subscriptions(): void
    {
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
        ]);
        
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::CANCELLED,
        ]);
        
        $subscriptions = $this->subscriptionService->getUserSubscriptions($this->user->id);
        
        $this->assertCount(2, $subscriptions);
    }

    /** @test */
    public function it_can_update_expired_subscriptions(): void
    {
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->subDay(),
        ]);
        
        $updatedCount = $this->subscriptionService->updateExpiredSubscriptions();
        
        $this->assertEquals(1, $updatedCount);
        $this->assertDatabaseHas('user_subscriptions_v2', [
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::EXPIRED,
        ]);
    }
}
