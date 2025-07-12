<?php

namespace App\Services;

use App\Models\WeekPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeekPlanService
{
    public function calculateProgress(array $meals, array $workouts): int
    {
        $total = count($meals) + count($workouts);
        if ($total === 0) {
            return 0;
        }

        $completed = collect($meals)->where('completed', true)->count() +
                    collect($workouts)->where('completed', true)->count();

        return (int) round(($completed / $total) * 100);
    }

    public function addMeal(WeekPlan $plan, array $mealData): WeekPlan
    {
        $meals = $plan->meals ?? [];
        $mealData['id'] = count($meals) + 1;
        $mealData['completed'] = false;
        $meals[] = $mealData;

        $plan->meals = $meals;
        $plan->progress = $this->calculateProgress($meals, $plan->workouts ?? []);
        $plan->save();

        return $plan;
    }

    public function addWorkout(WeekPlan $plan, array $data): WeekPlan
    {
        $workouts = $plan->workouts ?? [];

        // $data теперь содержит ['workout_id' => 'некий-uuid']
        $newPlannedWorkout = [
            'id' => count($workouts) + 1, // Локальный ID внутри этого плана для отметки выполнения
            'workout_id' => $data['workout_id'], // Ссылка на реальную тренировку
            'completed' => false,
        ];

        $workouts[] = $newPlannedWorkout;

        $plan->workouts = $workouts;
        $plan->progress = $this->calculateProgress($plan->meals ?? [], $workouts);
        $plan->save();

        return $plan;
    }

    public function markAsCompleted(WeekPlan $plan, string $type, int|string $id): WeekPlan
    {
        if (!in_array($type, ['meal', 'workout'])) {
            return $plan;
        }

        $property = $type . 's';
        $items = collect($plan->{$property})
            ->map(function ($item) use ($id, $type) {
                if ($type === 'workout') {
                    if ((string)$item['workout_id'] === (string)$id) {
                        $item['completed'] = !$item['completed'];
                    }
                } else { // meal
                    if ((string)$item['id'] === (string)$id) {
                        $item['completed'] = !$item['completed'];
                    }
                }
                return $item;
            })
            ->toArray();

        $plan->{$property} = $items;
        $plan->progress = $this->calculateProgress($plan->meals ?? [], $plan->workouts ?? []);
        $plan->save();

        return $plan;
    }
}
