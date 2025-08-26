<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function admin_panel_requires_authentication()
    {
        // Без аутентификации должны получить редирект на логин
        $response = $this->get('/admin');
        
        // Должны получить редирект на логин (302) или 403 Forbidden
        $this->assertTrue(
            in_array($response->status(), [302, 403]),
            "Expected status 302 or 403, got {$response->status()}"
        );
    }

    /** @test */
    public function can_access_admin_panel_with_admin_role()
    {
        // Создаем пользователя с ролью Admin
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Создаем роль Admin и назначаем её пользователю
        $role = \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        $user->assignRole($role);

        $this->actingAs($user);

        $response = $this->get('/admin');
        
        // С ролью Admin должны получить доступ
        $this->assertTrue(
            in_array($response->status(), [200, 302]),
            "Expected status 200 or 302, got {$response->status()}"
        );
    }

    /** @test */
    public function can_access_admin_panel_with_special_email()
    {
        // Создаем пользователя с специальным email
        $user = User::factory()->create([
            'email' => 'laravelka@yandex.ru',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin');
        
        // С специальным email должны получить доступ
        $this->assertTrue(
            in_array($response->status(), [200, 302]),
            "Expected status 200 or 302, got {$response->status()}"
        );
    }
}
