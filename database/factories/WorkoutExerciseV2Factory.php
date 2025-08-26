<?php

namespace Database\Factories;

use App\Models\WorkoutExerciseV2;
use App\Models\WorkoutProgramV2;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutExerciseV2>
 */
class WorkoutExerciseV2Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkoutExerciseV2::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        
        return [
            'program_id' => WorkoutProgramV2::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'instructions' => fake()->paragraphs(2, true),
            'sets' => fake()->numberBetween(1, 5),
            'reps' => fake()->numberBetween(8, 20),
            'duration_seconds' => fake()->numberBetween(30, 120),
            'rest_seconds' => fake()->numberBetween(30, 180),
            'weight_kg' => fake()->numberBetween(0, 100),
            'equipment_needed' => [
                'dumbbells' => 'Гантели различного веса',
                'bench' => 'Скамья для упражнений',
            ],
            'muscle_groups' => [
                'chest' => 'primary',
                'triceps' => 'secondary',
                'shoulders' => 'secondary',
            ],
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the exercise is for beginners.
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'sets' => fake()->numberBetween(1, 3),
            'reps' => fake()->numberBetween(8, 12),
            'weight_kg' => fake()->numberBetween(0, 20),
        ]);
    }

    /**
     * Indicate that the exercise is for intermediate users.
     */
    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'sets' => fake()->numberBetween(3, 4),
            'reps' => fake()->numberBetween(10, 15),
            'weight_kg' => fake()->numberBetween(10, 50),
        ]);
    }

    /**
     * Indicate that the exercise is for advanced users.
     */
    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'sets' => fake()->numberBetween(4, 5),
            'reps' => fake()->numberBetween(12, 20),
            'weight_kg' => fake()->numberBetween(30, 100),
        ]);
    }

    /**
     * Indicate that the exercise is bodyweight only.
     */
    public function bodyweight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight_kg' => 0,
            'equipment_needed' => [],
        ]);
    }

    /**
     * Indicate that the exercise is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
