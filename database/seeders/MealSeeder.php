<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Meal;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('Нет пользователей для создания приемов пищи. Запустите UserSeeder сначала.');
            return;
        }

        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

        foreach ($users as $user) {
            for ($i = 0; $i < 10; $i++) { // Создаем по 10 приемов пищи для каждого пользователя
                $date = Carbon::now()->subDays(rand(0, 30));
                $type = collect($mealTypes)->random();
                $calories = rand(200, 800);
                Meal::create([
                    'id' => Str::uuid()->toString(),
                    'user_id' => $user->id,
                    'name' => ucfirst($type) . ' пользователя ' . $user->id . ' #' . ($i + 1),
                    'type' => $type,
                    'calories' => $calories,
                    'proteins' => round($calories * 0.2 / 4), // Примерный расчет БЖУ
                    'fats' => round($calories * 0.3 / 9),
                    'carbs' => round($calories * 0.5 / 4),
                    'items' => json_encode([
                        [
                            'foodId' => 'example_food_1', 
                            'name' => 'Пример Еды 1', 
                            'quantity' => 1, 
                            'unit' => 'порция',
                            'calories' => round($calories * 0.6),
                            'proteins' => round($calories * 0.6 * 0.2 / 4),
                            'fats' => round($calories * 0.6 * 0.3 / 9),
                            'carbs' => round($calories * 0.6 * 0.5 / 4),
                        ],
                        [
                            'foodId' => 'example_food_2', 
                            'name' => 'Пример Еды 2', 
                            'quantity' => 100, 
                            'unit' => 'г',
                            'calories' => round($calories * 0.4),
                            'proteins' => round($calories * 0.4 * 0.2 / 4),
                            'fats' => round($calories * 0.4 * 0.3 / 9),
                            'carbs' => round($calories * 0.4 * 0.5 / 4),
                        ]
                    ]),
                    'date' => $date->toDateString(),
                    'completed' => rand(0, 1) == 1,
                ]);
            }
        }
    }
} 