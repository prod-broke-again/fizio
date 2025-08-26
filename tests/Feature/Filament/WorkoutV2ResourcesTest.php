<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutProgramV2;
use App\Models\WorkoutExerciseV2;
use App\Models\UserSubscriptionV2;
use App\Models\UserWorkoutProgressV2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkoutV2ResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем админа
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        
        // Создаем роль Admin и назначаем её пользователю
        $role = \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        $this->admin->assignRole($role);
    }

    /** @test */
    public function admin_can_access_workout_category_v2_resource()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/workout-category-v2s');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_workout_program_v2_resource()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/workout-program-v2s');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_workout_exercise_v2_resource()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/workout-exercise-v2s');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_user_subscription_v2_resource()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/user-subscription-v2s');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_user_workout_progress_v2_resource()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/user-workout-progress-v2s');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_workout_category()
    {
        $this->actingAs($this->admin);

        $categoryData = [
            'name' => 'Тестовая категория',
            'gender' => 'male',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
            'is_active' => true,
            'sort_order' => 1,
        ];

        $response = $this->post('/admin/workout-category-v2s', $categoryData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('workout_categories_v2', [
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
        ]);
    }

    /** @test */
    public function admin_can_create_workout_program()
    {
        $this->actingAs($this->admin);

        // Сначала создаем категорию
        $category = WorkoutCategoryV2::factory()->create();

        $programData = [
            'category_id' => $category->id,
            'name' => 'Тестовая программа',
            'slug' => 'test-program',
            'description' => 'Описание тестовой программы',
            'short_description' => 'Краткое описание',
            'difficulty_level' => 'beginner',
            'duration_weeks' => 4,
            'calories_per_workout' => 300,
            'is_free' => true,
            'is_active' => true,
            'sort_order' => 1,
        ];

        $response = $this->post('/admin/workout-program-v2s', $categoryData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('workout_programs_v2', [
            'name' => 'Тестовая программа',
            'slug' => 'test-program',
        ]);
    }

    /** @test */
    public function admin_can_create_workout_exercise()
    {
        $this->actingAs($this->admin);

        // Сначала создаем программу
        $program = WorkoutProgramV2::factory()->create();

        $exerciseData = [
            'program_id' => $program->id,
            'name' => 'Тестовое упражнение',
            'slug' => 'test-exercise',
            'description' => 'Описание тестового упражнения',
            'instructions' => 'Инструкции по выполнению',
            'sets' => 3,
            'reps' => 10,
            'duration_seconds' => 60,
            'rest_seconds' => 90,
            'weight_kg' => 20,
            'sort_order' => 1,
            'is_active' => true,
        ];

        $response = $this->post('/admin/workout-exercise-v2s', $exerciseData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('workout_exercises_v2', [
            'name' => 'Тестовое упражнение',
            'slug' => 'test-exercise',
        ]);
    }

    /** @test */
    public function admin_can_create_user_subscription()
    {
        $this->actingAs($this->admin);

        // Сначала создаем пользователя
        $user = User::factory()->create();

        $subscriptionData = [
            'user_id' => $user->id,
            'subscription_type' => 'monthly',
            'status' => 'active',
            'expires_at' => now()->addMonth(),
            'notes' => 'Тестовая подписка',
            'is_active' => true,
        ];

        $response = $this->post('/admin/user-subscription-v2s', $subscriptionData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('user_subscriptions_v2', [
            'user_id' => $user->id,
            'subscription_type' => 'monthly',
        ]);
    }

    /** @test */
    public function admin_can_create_user_workout_progress()
    {
        $this->actingAs($this->admin);

        // Сначала создаем необходимые модели
        $user = User::factory()->create();
        $program = WorkoutProgramV2::factory()->create();
        $exercise = WorkoutExerciseV2::factory()->create([
            'program_id' => $program->id,
        ]);

        $progressData = [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'exercise_id' => $exercise->id,
            'completed_at' => now(),
            'duration_seconds' => 300,
            'sets_completed' => 3,
            'reps_completed' => 10,
            'weight_used_kg' => 25,
            'calories_burned' => 150,
            'notes' => 'Тестовая тренировка',
            'is_completed' => true,
        ];

        $response = $this->post('/admin/user-workout-progress-v2s', $progressData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('user_workout_progress_v2', [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'exercise_id' => $exercise->id,
        ]);
    }
}
