<?php

declare(strict_types=1);

namespace Tests\Feature\V2;

use App\Enums\WorkoutGender;
use App\Models\User;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutProgramV2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutCategoryV2Test extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private WorkoutCategoryV2 $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = WorkoutCategoryV2::factory()->create([
            'gender' => WorkoutGender::MALE,
            'is_active' => true,
        ]);
        
        // Создаем программу для категории
        WorkoutProgramV2::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_get_all_active_categories(): void
    {
        $response = $this->getJson('/api/v2/workout-categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'gender',
                        'slug',
                        'description',
                        'is_active',
                        'sort_order',
                        'workout_programs',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_categories_by_gender(): void
    {
        $response = $this->getJson('/api/v2/workout-categories?gender=male');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.gender', 'male');
    }

    /** @test */
    public function it_can_get_specific_category_by_slug(): void
    {
        $response = $this->getJson("/api/v2/workout-categories/{$this->category->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'gender',
                    'slug',
                    'description',
                    'is_active',
                    'sort_order',
                    'workout_programs',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJsonPath('data.slug', $this->category->slug);
    }

    /** @test */
    public function it_returns_404_for_inactive_category(): void
    {
        $inactiveCategory = WorkoutCategoryV2::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->getJson("/api/v2/workout-categories/{$inactiveCategory->slug}");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_category(): void
    {
        $response = $this->getJson('/api/v2/workout-categories/nonexistent-slug');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_includes_programs_in_category_response(): void
    {
        $response = $this->getJson("/api/v2/workout-categories/{$this->category->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'workout_programs' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'description',
                            'difficulty_level',
                            'is_free',
                            'is_active',
                        ]
                    ]
                ]
            ]);
    }
}
