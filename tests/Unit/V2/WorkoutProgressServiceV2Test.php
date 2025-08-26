<?php

declare(strict_types=1);

namespace Tests\Unit\V2;

use App\Models\User;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutProgramV2;
use App\Models\WorkoutExerciseV2;
use App\Models\UserWorkoutProgressV2;
use App\Services\V2\WorkoutProgressServiceV2;
use App\Enums\WorkoutGender;
use App\Enums\WorkoutDifficulty;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutProgressServiceV2Test extends TestCase
{
    use RefreshDatabase;

    private WorkoutProgressServiceV2 $progressService;
    private User $user;
    private WorkoutCategoryV2 $category;
    private WorkoutProgramV2 $program;
    private WorkoutExerciseV2 $exercise;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->progressService = new WorkoutProgressServiceV2();
        
        $this->user = User::factory()->create();
        $this->category = WorkoutCategoryV2::factory()->create([
            'gender' => WorkoutGender::MALE,
            'is_active' => true,
        ]);
        
        $this->program = WorkoutProgramV2::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'difficulty_level' => WorkoutDifficulty::BEGINNER,
        ]);
        
        $this->exercise = WorkoutExerciseV2::factory()->create([
            'program_id' => $this->program->id,
        ]);
    }

    /** @test */
    public function it_can_get_user_progress(): void
    {
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'completed_at' => now(),
        ]);
        
        $progress = $this->progressService->getUserProgress($this->user->id);
        
        $this->assertCount(1, $progress);
        $this->assertEquals($this->user->id, $progress->first()->user_id);
        $this->assertEquals($this->program->id, $progress->first()->program_id);
    }

    /** @test */
    public function it_can_store_progress(): void
    {
        $progressData = [
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'duration_seconds' => 300,
            'notes' => 'Отличная тренировка!',
        ];
        
        $progress = $this->progressService->storeProgress($this->user->id, $progressData);
        
        $this->assertNotNull($progress);
        $this->assertEquals($this->user->id, $progress->user_id);
        $this->assertEquals($this->program->id, $progress->program_id);
        $this->assertEquals($this->exercise->id, $progress->exercise_id);
        $this->assertEquals(300, $progress->duration_seconds);
        $this->assertEquals('Отличная тренировка!', $progress->notes);
    }

    /** @test */
    public function it_can_update_progress(): void
    {
        $progress = UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
        ]);
        
        $updateData = [
            'duration_seconds' => 450,
            'notes' => 'Обновленные заметки',
        ];
        
        $updatedProgress = $this->progressService->updateProgress($progress, $updateData);
        
        $this->assertEquals(450, $updatedProgress->duration_seconds);
        $this->assertEquals('Обновленные заметки', $updatedProgress->notes);
    }

    /** @test */
    public function it_can_get_user_statistics(): void
    {
        // Создаем несколько тренировок
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'duration_seconds' => 300,
            'completed_at' => now(),
        ]);
        
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'duration_seconds' => 600,
            'completed_at' => now()->subDay(),
        ]);
        
        $statistics = $this->progressService->getUserStatistics($this->user->id);
        
        $this->assertEquals(2, $statistics['total_workouts']);
        $this->assertEquals(15.0, $statistics['total_duration_minutes']); // (300 + 600) / 60
        $this->assertEquals(1, $statistics['unique_programs']);
        $this->assertEquals(7.5, $statistics['average_duration_minutes']); // 15 / 2
        $this->assertArrayHasKey('weekly_stats', $statistics);
        $this->assertArrayHasKey('program_stats', $statistics);
        $this->assertArrayHasKey('current_streak', $statistics);
    }

    /** @test */
    public function it_can_get_program_progress(): void
    {
        // Создаем еще одно упражнение
        $exercise2 = WorkoutExerciseV2::factory()->create([
            'program_id' => $this->program->id,
        ]);
        
        // Пользователь завершил только одно упражнение
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
        ]);
        
        $programProgress = $this->progressService->getProgramProgress($this->user->id, $this->program->id);
        
        $this->assertEquals(2, $programProgress['total_exercises']);
        $this->assertEquals(1, $programProgress['completed_exercises']);
        $this->assertEquals(50.0, $programProgress['progress_percentage']);
        $this->assertEquals(1, $programProgress['remaining_exercises']);
    }

    /** @test */
    public function it_returns_zero_progress_for_new_program(): void
    {
        $programProgress = $this->progressService->getProgramProgress($this->user->id, $this->program->id);
        
        $this->assertEquals(1, $programProgress['total_exercises']);
        $this->assertEquals(0, $programProgress['completed_exercises']);
        $this->assertEquals(0.0, $programProgress['progress_percentage']);
        $this->assertEquals(1, $programProgress['remaining_exercises']);
    }

    /** @test */
    public function it_can_calculate_current_streak(): void
    {
        // Создаем тренировки в течение нескольких дней
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'completed_at' => now(),
        ]);
        
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'completed_at' => now()->subDay(),
        ]);
        
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'completed_at' => now()->subDays(2),
        ]);
        
        $statistics = $this->progressService->getUserStatistics($this->user->id);
        
        $this->assertEquals(3, $statistics['current_streak']);
    }

    /** @test */
    public function it_breaks_streak_on_missing_day(): void
    {
        // Создаем тренировки с пропуском дня
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'completed_at' => now(),
        ]);
        
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'completed_at' => now()->subDays(2), // Пропускаем вчера
        ]);
        
        $statistics = $this->progressService->getUserStatistics($this->user->id);
        
        $this->assertEquals(1, $statistics['current_streak']);
    }

    /** @test */
    public function it_handles_empty_progress(): void
    {
        $statistics = $this->progressService->getUserStatistics($this->user->id);
        
        $this->assertEquals(0, $statistics['total_workouts']);
        $this->assertEquals(0.0, $statistics['total_duration_minutes']);
        $this->assertEquals(0, $statistics['unique_programs']);
        $this->assertEquals(0.0, $statistics['average_duration_minutes']);
        $this->assertEquals(0, $statistics['current_streak']);
    }

    /** @test */
    public function it_can_handle_multiple_programs(): void
    {
        $program2 = WorkoutProgramV2::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);
        
        $exercise2 = WorkoutExerciseV2::factory()->create([
            'program_id' => $program2->id,
        ]);
        
        // Тренировки в разных программах
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
        ]);
        
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $program2->id,
            'exercise_id' => $exercise2->id,
        ]);
        
        $statistics = $this->progressService->getUserStatistics($this->user->id);
        
        $this->assertEquals(2, $statistics['total_workouts']);
        $this->assertEquals(2, $statistics['unique_programs']);
    }
}
