<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WorkoutSeeder extends Seeder
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
            $this->command->info('Нет пользователей для создания тренировок. Запустите UserSeeder сначала.');
            return;
        }

        foreach ($users as $user) {
            for ($i = 0; $i < 5; $i++) { // Создаем по 5 тренировок для каждого пользователя
                $date = Carbon::now()->subDays(rand(0, 30));
                Workout::create([
                    'id' => Str::uuid()->toString(),
                    'user_id' => $user->id,
                    'name' => 'Тестовая тренировка ' . ($i + 1) . ' для ' . $user->name,
                    'type' => collect(['strength', 'cardio', 'hiit', 'flexibility'])->random(),
                    'exercises' => json_encode([
                        [
                            'exerciseId' => 'push_ups', 
                            'sets' => [['reps' => 10], ['reps' => 10], ['reps' => 8]], 
                            'order' => 0, 
                            'restTime' => 60
                        ],
                        [
                            'exerciseId' => 'squats', 
                            'sets' => [['reps' => 12], ['reps' => 12], ['reps' => 10]], 
                            'order' => 1, 
                            'restTime' => 90
                        ]
                    ]),
                    'duration' => rand(30, 90),
                    'difficulty' => collect(['beginner', 'intermediate', 'advanced'])->random(),
                    'date' => $date->toDateString(),
                    'completed' => rand(0, 1) == 1,
                    'calories_burned' => rand(0, 1) == 1 ? rand(150, 500) : null,
                ]);
            }
        }
    }
} 