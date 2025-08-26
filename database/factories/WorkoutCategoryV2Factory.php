<?php

namespace Database\Factories;

use App\Enums\WorkoutGender;
use App\Models\WorkoutCategoryV2;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutCategoryV2>
 */
class WorkoutCategoryV2Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkoutCategoryV2::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        
        return [
            'name' => ucfirst($name),
            'gender' => fake()->randomElement(WorkoutGender::cases()),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the category is for males.
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => WorkoutGender::MALE,
        ]);
    }

    /**
     * Indicate that the category is for females.
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => WorkoutGender::FEMALE,
        ]);
    }

    /**
     * Indicate that the category is unisex.
     */
    public function unisex(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => WorkoutGender::UNISEX,
        ]);
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
