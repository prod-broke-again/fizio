<?php

declare(strict_types=1);

namespace Tests\Unit\V2;

use App\Enums\WorkoutDifficulty;
use App\Enums\WorkoutGender;
use App\Models\User;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutProgramV2;
use App\Models\UserSubscriptionV2;
use App\Services\V2\WorkoutServiceV2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutServiceV2Test extends TestCase
{
    use RefreshDatabase;

    private WorkoutServiceV2 $workoutService;
    private User $user;
    private WorkoutCategoryV2 $category;
    private WorkoutProgramV2 $freeProgram;
    private WorkoutProgramV2 $paidProgram;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->workoutService = new WorkoutServiceV2();
        
        $this->user = User::factory()->create();
        $this->category = WorkoutCategoryV2::factory()->create([
            'gender' => WorkoutGender::MALE,
            'is_active' => true,
        ]);
        
        $this->freeProgram = WorkoutProgramV2::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'is_free' => true,
            'difficulty_level' => WorkoutDifficulty::BEGINNER,
        ]);
        
        $this->paidProgram = WorkoutProgramV2::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'is_free' => false,
            'difficulty_level' => WorkoutDifficulty::INTERMEDIATE,
        ]);
    }

    /** @test */
    public function it_can_get_filtered_programs(): void
    {
        $filters = ['difficulty_level' => 'beginner'];
        
        $programs = $this->workoutService->getFilteredPrograms($filters);
        
        $this->assertCount(1, $programs);
        $this->assertEquals($this->freeProgram->id, $programs->first()->id);
    }

    /** @test */
    public function it_can_filter_programs_by_free_status(): void
    {
        $filters = ['is_free' => true];
        
        $programs = $this->workoutService->getFilteredPrograms($filters);
        
        $this->assertCount(1, $programs);
        $this->assertTrue($programs->first()->is_free);
    }

    /** @test */
    public function it_can_filter_programs_by_gender(): void
    {
        $filters = ['gender' => 'male'];
        
        $programs = $this->workoutService->getFilteredPrograms($filters);
        
        $this->assertCount(2, $programs);
        $this->assertEquals('male', $programs->first()->category->gender);
    }

    /** @test */
    public function it_can_combine_multiple_filters(): void
    {
        $filters = [
            'difficulty_level' => 'beginner',
            'is_free' => true,
            'gender' => 'male'
        ];
        
        $programs = $this->workoutService->getFilteredPrograms($filters);
        
        $this->assertCount(1, $programs);
        $this->assertEquals($this->freeProgram->id, $programs->first()->id);
    }

    /** @test */
    public function it_returns_all_programs_when_no_filters(): void
    {
        $programs = $this->workoutService->getFilteredPrograms();
        
        $this->assertCount(2, $programs);
    }

    /** @test */
    public function it_can_get_programs_by_user_gender(): void
    {
        $programs = $this->workoutService->getProgramsByUserGender($this->user);
        
        $this->assertCount(2, $programs);
        $this->assertEquals('male', $programs->first()->category->gender);
    }

    /** @test */
    public function it_can_get_free_programs(): void
    {
        $programs = $this->workoutService->getFreePrograms();
        
        $this->assertCount(1, $programs);
        $this->assertTrue($programs->first()->is_free);
    }

    /** @test */
    public function it_can_get_programs_by_difficulty(): void
    {
        $programs = $this->workoutService->getProgramsByDifficulty(WorkoutDifficulty::BEGINNER);
        
        $this->assertCount(1, $programs);
        $this->assertEquals(WorkoutDifficulty::BEGINNER, $programs->first()->difficulty_level);
    }

    /** @test */
    public function it_allows_access_to_free_programs(): void
    {
        $canAccess = $this->workoutService->canUserAccessProgram($this->user, $this->freeProgram);
        
        $this->assertTrue($canAccess);
    }

    /** @test */
    public function it_denies_access_to_paid_programs_without_subscription(): void
    {
        $canAccess = $this->workoutService->canUserAccessProgram($this->user, $this->paidProgram);
        
        $this->assertFalse($canAccess);
    }

    /** @test */
    public function it_allows_access_to_paid_programs_with_active_subscription(): void
    {
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'expires_at' => now()->addMonth(),
        ]);
        
        $canAccess = $this->workoutService->canUserAccessProgram($this->user, $this->paidProgram);
        
        $this->assertTrue($canAccess);
    }

    /** @test */
    public function it_denies_access_to_paid_programs_with_expired_subscription(): void
    {
        UserSubscriptionV2::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'expires_at' => now()->subDay(),
        ]);
        
        $canAccess = $this->workoutService->canUserAccessProgram($this->user, $this->paidProgram);
        
        $this->assertFalse($canAccess);
    }

    /** @test */
    public function it_can_get_recommended_programs(): void
    {
        $programs = $this->workoutService->getRecommendedPrograms($this->user, 3);
        
        $this->assertCount(1, $programs);
        $this->assertTrue($programs->first()->is_free);
        $this->assertEquals('male', $programs->first()->category->gender);
    }

    /** @test */
    public function it_limits_recommended_programs(): void
    {
        // Создаем еще одну бесплатную программу
        WorkoutProgramV2::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'is_free' => true,
        ]);
        
        $programs = $this->workoutService->getRecommendedPrograms($this->user, 1);
        
        $this->assertCount(1, $programs);
    }

    /** @test */
    public function it_can_clear_cache(): void
    {
        // Этот тест проверяет, что метод не вызывает ошибок
        $this->workoutService->clearCache();
        
        $this->assertTrue(true); // Метод выполнился без ошибок
    }
}
