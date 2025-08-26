<?php

declare(strict_types=1);

namespace Tests\Feature\V2;

use App\Enums\WorkoutDifficulty;
use App\Enums\WorkoutGender;
use App\Models\User;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutProgramV2;
use App\Models\WorkoutExerciseV2;
use App\Models\UserWorkoutProgressV2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserWorkoutProgressV2Test extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private WorkoutCategoryV2 $category;
    private WorkoutProgramV2 $program;
    private WorkoutExerciseV2 $exercise;

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
            'difficulty_level' => WorkoutDifficulty::BEGINNER,
        ]);
        
        $this->exercise = WorkoutExerciseV2::factory()->create([
            'program_id' => $this->program->id,
        ]);
        
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_get_user_progress(): void
    {
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
        ]);

        $response = $this->getJson('/api/v2/user/workout-progress');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'program',
                        'exercise',
                        'completed_at',
                        'duration_seconds',
                        'notes',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_store_workout_progress(): void
    {
        $progressData = [
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'duration_seconds' => 300,
            'notes' => 'Отличная тренировка!',
        ];

        $response = $this->postJson('/api/v2/user/workout-progress', $progressData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'program_id',
                    'exercise_id',
                    'duration_seconds',
                    'notes',
                ]
            ])
            ->assertJsonPath('data.duration_seconds', 300)
            ->assertJsonPath('data.notes', 'Отличная тренировка!');

        $this->assertDatabaseHas('user_workout_progress_v2', [
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'duration_seconds' => 300,
        ]);
    }

    /** @test */
    public function it_can_update_workout_progress(): void
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

        $response = $this->putJson("/api/v2/user/workout-progress/{$progress->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'duration_seconds',
                    'notes',
                ]
            ])
            ->assertJsonPath('data.duration_seconds', 450)
            ->assertJsonPath('data.notes', 'Обновленные заметки');
    }

    /** @test */
    public function it_can_get_user_statistics(): void
    {
        UserWorkoutProgressV2::factory()->create([
            'user_id' => $this->user->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'duration_seconds' => 300,
        ]);

        $response = $this->getJson('/api/v2/user/workout-progress/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_workouts',
                    'total_duration_minutes',
                    'unique_programs',
                    'average_duration_minutes',
                    'weekly_stats',
                    'program_stats',
                    'current_streak',
                ]
            ])
            ->assertJsonPath('data.total_workouts', 1)
            ->assertJsonPath('data.total_duration_minutes', 5.0);
    }

    /** @test */
    public function it_validates_required_fields_when_storing(): void
    {
        $response = $this->postJson('/api/v2/user/workout-progress', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_id', 'exercise_id']);
    }

    /** @test */
    public function it_validates_program_exists(): void
    {
        $progressData = [
            'program_id' => 99999,
            'exercise_id' => $this->exercise->id,
        ];

        $response = $this->postJson('/api/v2/user/workout-progress', $progressData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['program_id']);
    }

    /** @test */
    public function it_validates_exercise_exists(): void
    {
        $progressData = [
            'program_id' => $this->program->id,
            'exercise_id' => 99999,
        ];

        $response = $this->postJson('/api/v2/user/workout-progress', $progressData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['exercise_id']);
    }

    /** @test */
    public function it_validates_duration_seconds_range(): void
    {
        $progressData = [
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
            'duration_seconds' => -1,
        ];

        $response = $this->postJson('/api/v2/user/workout-progress', $progressData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['duration_seconds']);
    }

    /** @test */
    public function it_prevents_updating_other_user_progress(): void
    {
        $otherUser = User::factory()->create();
        $otherProgress = UserWorkoutProgressV2::factory()->create([
            'user_id' => $otherUser->id,
            'program_id' => $this->program->id,
            'exercise_id' => $this->exercise->id,
        ]);

        $updateData = ['duration_seconds' => 600];

        $response = $this->putJson("/api/v2/user/workout-progress/{$otherProgress->id}", $updateData);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/v2/user/workout-progress');

        $response->assertStatus(401);
    }
}
