<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Goal;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GoalSeeder extends Seeder
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
            $this->command->info('Нет пользователей для создания целей. Запустите UserSeeder сначала.');
            return;
        }

        foreach ($users as $user) {
            Goal::create([
                'id' => Str::uuid()->toString(),
                'user_id' => $user->id,
                'name' => 'Снизить вес до 70 кг',
                'type' => 'weight',
                'target_value' => 70,
                'current_value' => $user->progress()->latest('date')->first()->weight ?? 75, // Берем последний вес или дефолт
                'start_value' => $user->progress()->oldest('date')->first()->weight ?? 78, // Берем первый вес или дефолт
                'unit' => 'kg',
                'target_date' => Carbon::now()->addMonths(3)->toDateString(),
                'status' => 'active',
                'notes' => json_encode([
                    ['date' => Carbon::now()->subWeek()->toDateString(), 'value' => 75.5, 'note' => 'Начало диеты'],
                    ['date' => Carbon::now()->toDateString(), 'value' => 75, 'note' => 'Прошла неделя'],
                ]),
            ]);

            Goal::create([
                'id' => Str::uuid()->toString(),
                'user_id' => $user->id,
                'name' => 'Пробежать 5 км',
                'type' => 'run_distance',
                'target_value' => 5,
                'current_value' => 1.5,
                'start_value' => 0.5,
                'unit' => 'km',
                'target_date' => Carbon::now()->addMonths(2)->toDateString(),
                'status' => 'active',
                'notes' => json_encode([
                    ['date' => Carbon::now()->subDays(3)->toDateString(), 'value' => 1, 'note' => 'Первая пробежка после перерыва'],
                ]),
            ]);
        }
    }
} 