<?php

declare(strict_types=1);

namespace Tests\Feature\V2;

use App\Enums\WorkoutDifficulty;
use App\Enums\WorkoutGender;
use App\Models\User;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutProgramV2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutProgramV2Test extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private WorkoutCategoryV2 $category;
    private WorkoutProgramV2 $program;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = WorkoutCategoryV2::factory()->create([
            'gender' => WorkoutGender::MALE,
            'is_active' => true,
        ]);
        
        $this->program = WorkoutProgramV2::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'is_free' => true,
            'difficulty_level' => WorkoutDifficulty::BEGINNER,
        ]);
    }

    /** @test */
    public function it_can_get_all_active_programs(): void
    {
        $response = $this->getJson('/api/v2/workout-programs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'difficulty_level',
                        'is_free',
                        'is_active',
                        'category',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_programs_by_difficulty(): void
    {
        $response = $this->getJson('/api/v2/workout-programs?difficulty_level=beginner');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.difficulty_level', 'beginner');
    }

    /** @test */
    public function it_can_filter_programs_by_free_status(): void
    {
        $response = $this->getJson('/api/v2/workout-programs?is_free=true');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_free', true);
    }

    /** @test */
    public function it_can_filter_programs_by_gender(): void
    {
        $response = $this->getJson('/api/v2/workout-programs?gender=male');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_get_specific_program_by_slug(): void
    {
        $response = $this->getJson("/api/v2/workout-programs/{$this->program->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'difficulty_level',
                    'is_free',
                    'is_active',
                    'category',
                    'workout_exercises',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJsonPath('data.slug', $this->program->slug);
    }

    /** @test */
    public function it_can_get_programs_by_category(): void
    {
        $response = $this->getJson("/api/v2/workout-programs/category/{$this->category->slug}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category.slug', $this->category->slug);
    }

    /** @test */
    public function it_returns_404_for_inactive_program(): void
    {
        $inactiveProgram = WorkoutProgramV2::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->getJson("/api/v2/workout-programs/{$inactiveProgram->slug}");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_program(): void
    {
        $response = $this->getJson('/api/v2/workout-programs/nonexistent-slug');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_includes_category_in_program_response(): void
    {
        $response = $this->getJson("/api/v2/workout-programs/{$this->program->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'category' => [
                        'id',
                        'name',
                        'gender',
                        'slug',
                        'is_active',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_combine_multiple_filters(): void
    {
        $response = $this->getJson('/api/v2/workout-programs?difficulty_level=beginner&is_free=true&gender=male');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
