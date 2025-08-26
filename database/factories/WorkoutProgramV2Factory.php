<?php

namespace Database\Factories;

use App\Enums\WorkoutDifficulty;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutProgramV2;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutProgramV2>
 */
class WorkoutProgramV2Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkoutProgramV2::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        
        return [
            'category_id' => WorkoutCategoryV2::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(2, true),
            'short_description' => fake()->sentence(),
            'difficulty_level' => fake()->randomElement(WorkoutDifficulty::cases()),
            'duration_weeks' => fake()->numberBetween(1, 12),
            'calories_per_workout' => fake()->numberBetween(200, 800),
            'is_free' => fake()->boolean(30), // 30% бесплатных
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the program is for beginners.
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => WorkoutDifficulty::BEGINNER,
        ]);
    }

    /**
     * Indicate that the program is for intermediate users.
     */
    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => WorkoutDifficulty::INTERMEDIATE,
        ]);
    }

    /**
     * Indicate that the program is for advanced users.
     */
    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => WorkoutDifficulty::ADVANCED,
        ]);
    }

    /**
     * Indicate that the program is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_free' => true,
        ]);
    }

    /**
     * Indicate that the program is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_free' => false,
        ]);
    }

    /**
     * Indicate that the program is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
