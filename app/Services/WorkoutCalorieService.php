<?php

namespace App\Services;

class WorkoutCalorieService
{
    /**
     * Расчет калорий с учетом MET, веса, пола, длительности и сложности
     * Если передан actualCalories (например, с Apple Health/HealthKit) — возвращает его
     *
     * @param string $type Тип тренировки (cardio, strength, flexibility и т.д.)
     * @param int $duration Длительность в минутах
     * @param string $difficulty Сложность (beginner, intermediate, advanced)
     * @param float $weight Вес пользователя в кг
     * @param string $gender Пол пользователя ('male' или 'female')
     * @param int|null $actualCalories Реальное значение калорий с устройства (если есть)
     * @return int
     */
    public function calculate(
        string $type,
        int $duration,
        string $difficulty,
        float $weight,
        string $gender = 'male',
        ?int $actualCalories = null
    ): int {
        if ($actualCalories !== null) {
            return $actualCalories;
        }

        // Таблица MET
        $metTable = [
            'cardio' => [
                'beginner' => 6,
                'intermediate' => 8,
                'advanced' => 10,
            ],
            'strength' => [
                'beginner' => 5,
                'intermediate' => 6,
                'advanced' => 8,
            ],
            'flexibility' => [
                'beginner' => 2.5,
                'intermediate' => 3,
                'advanced' => 4,
            ],
        ];

        $met = $metTable[$type][$difficulty] ?? 5;
        $hours = $duration / 60;
        $genderCoef = ($gender === 'female') ? 0.95 : 1.0;

        $calories = $met * $weight * $hours * $genderCoef;
        return (int)round($calories);
    }
} 