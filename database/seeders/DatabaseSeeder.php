<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            WorkoutSeeder::class,
            MealSeeder::class,
            ProgressSeeder::class, // ProgressSeeder может использовать данные пользователей
            GoalSeeder::class,     // GoalSeeder может использовать данные пользователей и их прогресса
            // Добавьте сюда другие сидеры, если они есть
        ]);
    }
}
