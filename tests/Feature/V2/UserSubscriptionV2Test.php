<?php

declare(strict_types=1);

namespace Tests\Feature\V2;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Models\User;
use App\Models\UserSubscriptionV2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserSubscriptionV2Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_get_user_subscription(): void
    {
        $subscription = UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addMonth(),
        ]);

        $response = $this->getJson('/api/v2/user/subscription');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'subscription_type',
                    'status',
                    'starts_at',
                    'expires_at',
                    'days_remaining',
                    'is_active',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJsonPath('data.id', $subscription->id)
            ->assertJsonPath('data.is_active', true);
    }

    /** @test */
    public function it_returns_null_when_no_subscription(): void
    {
        $response = $this->getJson('/api/v2/user/subscription');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ])
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'У пользователя нет активной подписки');
    }

    /** @test */
    public function it_can_create_subscription(): void
    {
        $subscriptionData = [
            'subscription_type' => SubscriptionType::MONTHLY,
        ];

        $response = $this->postJson('/api/v2/user/subscription', $subscriptionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'subscription_type',
                    'status',
                    'starts_at',
                    'expires_at',
                ]
            ])
            ->assertJsonPath('data.subscription_type', 'monthly')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('user_subscriptions_v2', [
            'user_id' => $this->user->id,
            'subscription_type' => 'monthly',
            'status' => 'active',
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

        $response = $this->deleteJson('/api/v2/user/subscription/cancel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJsonPath('message', 'Подписка успешно отменена');

        $this->assertDatabaseHas('user_subscriptions_v2', [
            'id' => $subscription->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_can_get_subscription_status(): void
    {
        $subscription = UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addDays(15),
        ]);

        $response = $this->getJson('/api/v2/user/subscription/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'has_active_subscription',
                    'subscription_type',
                    'expires_at',
                    'days_remaining',
                    'is_expired',
                ]
            ])
            ->assertJsonPath('data.has_active_subscription', true)
            ->assertJsonPath('data.subscription_type', $subscription->subscription_type)
            ->assertJsonPath('data.days_remaining', 15)
            ->assertJsonPath('data.is_expired', false);
    }

    /** @test */
    public function it_validates_subscription_type_when_creating(): void
    {
        $response = $this->postJson('/api/v2/user/subscription', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subscription_type']);
    }

    /** @test */
    public function it_validates_subscription_type_enum(): void
    {
        $response = $this->postJson('/api/v2/user/subscription', [
            'subscription_type' => 'invalid_type',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subscription_type']);
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
        $response = $this->postJson('/api/v2/user/subscription', [
            'subscription_type' => SubscriptionType::YEARLY,
        ]);

        $response->assertStatus(201);

        // Проверяем, что первая подписка отменена
        $this->assertDatabaseHas('user_subscriptions_v2', [
            'id' => $firstSubscription->id,
            'status' => 'cancelled',
        ]);

        // Проверяем, что новая подписка активна
        $this->assertDatabaseHas('user_subscriptions_v2', [
            'user_id' => $this->user->id,
            'subscription_type' => 'yearly',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_returns_error_when_cancelling_nonexistent_subscription(): void
    {
        $response = $this->deleteJson('/api/v2/user/subscription/cancel');

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message'
            ])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Не удалось отменить подписку');
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/v2/user/subscription');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_calculates_correct_expiration_dates(): void
    {
        $monthlyResponse = $this->postJson('/api/v2/user/subscription', [
            'subscription_type' => SubscriptionType::MONTHLY,
        ]);

        $monthlyResponse->assertStatus(201);
        
        $monthlySubscription = UserSubscriptionV2::where('user_id', $this->user->id)
            ->where('subscription_type', 'monthly')
            ->first();
            
        $this->assertEquals(
            now()->addMonth()->format('Y-m-d'),
            $monthlySubscription->expires_at->format('Y-m-d')
        );

        // Создаем годовую подписку
        $yearlyResponse = $this->postJson('/api/v2/user/subscription', [
            'subscription_type' => SubscriptionType::YEARLY,
        ]);

        $yearlyResponse->assertStatus(201);
        
        $yearlySubscription = UserSubscriptionV2::where('user_id', $this->user->id)
            ->where('subscription_type', 'yearly')
            ->first();
            
        $this->assertEquals(
            now()->addYear()->format('Y-m-d'),
            $yearlySubscription->expires_at->format('Y-m-d')
        );
    }
}
