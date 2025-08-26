<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\WorkoutDifficulty;
use App\Enums\WorkoutGender;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutExerciseV2;
use App\Models\WorkoutProgramV2;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Сидер для тестовых данных V2 системы тренировок
 */
class WorkoutV2Seeder extends Seeder
{
    /**
     * Запустить сидер
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedPrograms();
        $this->seedExercises();
    }

    /**
     * Создать категории тренировок
     */
    private function seedCategories(): void
    {
        $categories = [
            // Мужские категории
            [
                'name' => 'Набор веса',
                'gender' => WorkoutGender::MALE,
                'slug' => 'male-weight-gain',
                'description' => 'Программы для набора мышечной массы для мужчин',
                'sort_order' => 1,
            ],
            [
                'name' => 'Похудение',
                'gender' => WorkoutGender::MALE,
                'slug' => 'male-weight-loss',
                'description' => 'Программы для снижения веса для мужчин',
                'sort_order' => 2,
            ],
            [
                'name' => 'Удержание формы',
                'gender' => WorkoutGender::MALE,
                'slug' => 'male-maintenance',
                'description' => 'Программы для поддержания формы для мужчин',
                'sort_order' => 3,
            ],
            [
                'name' => 'Силовая подготовка',
                'gender' => WorkoutGender::MALE,
                'slug' => 'male-strength',
                'description' => 'Программы для развития силы для мужчин',
                'sort_order' => 4,
            ],
            [
                'name' => 'Выносливость',
                'gender' => WorkoutGender::MALE,
                'slug' => 'male-endurance',
                'description' => 'Программы для развития выносливости для мужчин',
                'sort_order' => 5,
            ],

            // Женские категории
            [
                'name' => 'Набор веса',
                'gender' => WorkoutGender::FEMALE,
                'slug' => 'female-weight-gain',
                'description' => 'Программы для набора мышечной массы для женщин',
                'sort_order' => 1,
            ],
            [
                'name' => 'Похудение',
                'gender' => WorkoutGender::FEMALE,
                'slug' => 'female-weight-loss',
                'description' => 'Программы для снижения веса для женщин',
                'sort_order' => 2,
            ],
            [
                'name' => 'Удержание формы',
                'gender' => WorkoutGender::FEMALE,
                'slug' => 'female-maintenance',
                'description' => 'Программы для поддержания формы для женщин',
                'sort_order' => 3,
            ],
            [
                'name' => 'Йога и растяжка',
                'gender' => WorkoutGender::FEMALE,
                'slug' => 'female-yoga',
                'description' => 'Программы йоги и растяжки для женщин',
                'sort_order' => 4,
            ],
            [
                'name' => 'Специальные программы',
                'gender' => WorkoutGender::FEMALE,
                'slug' => 'female-special',
                'description' => 'Специальные программы тренировок для женщин',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $categoryData) {
            WorkoutCategoryV2::create($categoryData);
        }

        $this->command->info('Создано ' . count($categories) . ' категорий тренировок V2');
    }

    /**
     * Создать программы тренировок
     */
    private function seedPrograms(): void
    {
        $categories = WorkoutCategoryV2::all();

        foreach ($categories as $category) {
            $this->createProgramsForCategory($category);
        }

        $this->command->info('Созданы программы тренировок V2 для всех категорий');
    }

    /**
     * Создать программы для конкретной категории
     */
    private function createProgramsForCategory(WorkoutCategoryV2 $category): void
    {
        $programs = [
            [
                'name' => 'Базовый курс',
                'slug' => $category->slug . '-basic',
                'description' => 'Базовый курс тренировок для начинающих',
                'short_description' => 'Идеально для новичков',
                'difficulty_level' => WorkoutDifficulty::BEGINNER,
                'duration_weeks' => 4,
                'calories_per_workout' => 200,
                'is_free' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Продвинутый курс',
                'slug' => $category->slug . '-advanced',
                'description' => 'Продвинутый курс для опытных спортсменов',
                'short_description' => 'Для тех, кто готов к серьезным нагрузкам',
                'difficulty_level' => WorkoutDifficulty::ADVANCED,
                'duration_weeks' => 8,
                'calories_per_workout' => 400,
                'is_free' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Интенсивный курс',
                'slug' => $category->slug . '-intensive',
                'description' => 'Интенсивный курс для быстрого результата',
                'short_description' => 'Максимальная эффективность',
                'difficulty_level' => WorkoutDifficulty::INTERMEDIATE,
                'duration_weeks' => 6,
                'calories_per_workout' => 300,
                'is_free' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($programs as $programData) {
            $programData['category_id'] = $category->id;
            WorkoutProgramV2::create($programData);
        }
    }

    /**
     * Создать упражнения
     */
    private function seedExercises(): void
    {
        $programs = WorkoutProgramV2::all();

        foreach ($programs as $program) {
            $this->createExercisesForProgram($program);
        }

        $this->command->info('Созданы упражнения V2 для всех программ');
    }

    /**
     * Создать упражнения для конкретной программы
     */
    private function createExercisesForProgram(WorkoutProgramV2 $program): void
    {
        $exercises = [
            [
                'name' => 'Разминка',
                'description' => 'Базовая разминка для подготовки мышц',
                'duration_seconds' => 300, // 5 минут
                'sets' => 1,
                'reps' => 1,
                'rest_seconds' => 0,
                'equipment_needed' => [],
                'muscle_groups' => ['Общая подготовка'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Основное упражнение 1',
                'description' => 'Первое основное упражнение программы',
                'duration_seconds' => 600, // 10 минут
                'sets' => 3,
                'reps' => 12,
                'rest_seconds' => 60,
                'equipment_needed' => ['Гантели', 'Коврик'],
                'muscle_groups' => ['Грудь', 'Плечи'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Основное упражнение 2',
                'description' => 'Второе основное упражнение программы',
                'duration_seconds' => 600, // 10 минут
                'sets' => 3,
                'reps' => 15,
                'rest_seconds' => 60,
                'equipment_needed' => ['Скамья', 'Гантели'],
                'muscle_groups' => ['Спина', 'Бицепс'],
                'sort_order' => 3,
            ],
            [
                'name' => 'Заминка',
                'description' => 'Растяжка и расслабление мышц',
                'duration_seconds' => 300, // 5 минут
                'sets' => 1,
                'reps' => 1,
                'rest_seconds' => 0,
                'equipment_needed' => ['Коврик'],
                'muscle_groups' => ['Общая растяжка'],
                'sort_order' => 4,
            ],
        ];

        foreach ($exercises as $exerciseData) {
            $exerciseData['program_id'] = $program->id;
            WorkoutExerciseV2::create($exerciseData);
        }
    }
}
