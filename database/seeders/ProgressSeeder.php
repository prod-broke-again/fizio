<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Progress;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProgressSeeder extends Seeder
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
            $this->command->info('Нет пользователей для создания записей прогресса. Запустите UserSeeder сначала.');
            return;
        }

        foreach ($users as $user) {
            for ($i = 0; $i < 30; $i++) { // Создаем по 30 записей прогресса (имитируем месяц)
                $date = Carbon::now()->subDays($i);
                Progress::create([
                    'id' => Str::uuid()->toString(),
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'calories' => rand(1500, 3000),
                    'steps' => rand(3000, 15000),
                    'workout_time' => rand(0, 1) == 1 ? rand(20, 120) : null,
                    'water_intake' => rand(15, 40) / 10.0, // 1.5 to 4.0 liters
                    'weight' => round(70 + ($user->id * 5) - ($i / 5) + (rand(-10, 10) / 10), 1), // Небольшая динамика веса
                    'body_fat_percentage' => rand(0,1) == 1 ? round(15 + ($user->id * 2) - ($i / 10) + (rand(-5, 5) / 10), 1) : null,
                    'measurements' => rand(0,1) == 1 ? json_encode([
                        'chest' => round(90 + $user->id - ($i / 10) + rand(-5,5)/10, 1),
                        'waist' => round(70 + $user->id - ($i / 8) + rand(-5,5)/10, 1),
                        'hips' => round(95 + $user->id - ($i / 10) + rand(-5,5)/10, 1),
                    ]) : null,
                    'photos' => null, // Пока не добавляем фото в сидер
                ]);
            }
        }
    }
} 