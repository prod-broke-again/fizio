<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест получения профиля пользователя.
     */
    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'gender' => 'male',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user/profile');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'gender',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Профиль пользователя',
                'data' => [
                    'user' => [
                        'name' => 'Тестовый Пользователь',
                        'email' => 'test@example.com',
                        'gender' => 'male',
                    ]
                ]
            ]);
    }

    /**
     * Тест сохранения цели фитнеса.
     */
    public function test_user_can_save_fitness_goal(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/user/fitness-goal', [
            'goal' => 'weight-loss',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'goal',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Цель фитнеса сохранена',
                'data' => [
                    'goal' => 'weight-loss',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fitness_goal' => 'weight-loss',
        ]);
    }

    /**
     * Тест сохранения цели фитнеса с некорректными данными.
     */
    public function test_user_cannot_save_invalid_fitness_goal(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/user/fitness-goal', [
            'goal' => 'invalid-goal',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['goal']);
    }

    /**
     * Тест получения цели фитнеса.
     */
    public function test_user_can_get_fitness_goal(): void
    {
        $user = User::factory()->create([
            'fitness_goal' => 'muscle-gain',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user/fitness-goal');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'goal',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Текущая цель фитнеса',
                'data' => [
                    'goal' => 'muscle-gain',
                ]
            ]);
    }
} 