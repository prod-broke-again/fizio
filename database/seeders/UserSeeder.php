<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Тестовый Пользователь 1',
            'email' => 'user1@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        User::create([
            'name' => 'Тестовый Пользователь 2',
            'email' => 'user2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        // Можно добавить больше пользователей или использовать фабрику
        // User::factory(10)->create(); 
        // Убедись, что фабрика UserFactory.php настроена, если будешь использовать ее.
    }
} 