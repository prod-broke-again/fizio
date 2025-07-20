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
        $user1 = User::create([
            'name' => 'Eugene',
            'email' => 'laravelka@yandex.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('Qaz-xsw102'),
            'remember_token' => Str::random(10),
        ]);
        $user1->assignRole('admin');

        $user2 = User::create([
            'name' => 'Petr',
            'email' => 'petr@fizio.online',
            'email_verified_at' => now(),
            'password' => Hash::make('Qaz-xsw102'),
            'remember_token' => Str::random(10),
        ]);
        $user2->assignRole('admin');

        // Можно добавить больше пользователей или использовать фабрику
        // User::factory(10)->create(); 
        // Убедись, что фабрика UserFactory.php настроена, если будешь использовать ее.
    }
} 