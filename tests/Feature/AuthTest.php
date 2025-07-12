<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест регистрации пользователя.
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_token' => 'test-device-token'
        ]);

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
                        'created_at',
                        'updated_at'
                    ],
                    'access_token',
                    'token_type'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Регистрация успешно завершена'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Тестовый Пользователь',
            'gender' => null,
            'device_token' => 'test-device-token'
        ]);
    }

    /**
     * Тест регистрации с некорректными данными.
     */
    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Тест входа пользователя.
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_token' => 'new-device-token'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'access_token',
                    'token_type'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Авторизация успешна'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'device_token' => 'new-device-token'
        ]);
    }

    /**
     * Тест неверных учетных данных при входе.
     */
    public function test_user_cannot_login_with_incorrect_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Неверный email или пароль'
            ]);
    }

    /**
     * Тест выхода пользователя.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/auth/logout');

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Выход выполнен успешно'
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
} 