<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\WorkoutCategoryV2Resource;
use App\Filament\Resources\WorkoutProgramV2Resource;
use App\Filament\Resources\WorkoutExerciseV2Resource;
use App\Filament\Resources\UserSubscriptionV2Resource;
use App\Filament\Resources\UserWorkoutProgressV2Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutV2ResourcesLoadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function workout_category_v2_resource_can_be_instantiated()
    {
        $resource = new WorkoutCategoryV2Resource();
        
        $this->assertInstanceOf(WorkoutCategoryV2Resource::class, $resource);
        $this->assertEquals('Тренировки V2', $resource::getNavigationGroup());
        $this->assertEquals('Категории тренировок', $resource::getNavigationLabel());
    }

    /** @test */
    public function workout_program_v2_resource_can_be_instantiated()
    {
        $resource = new WorkoutProgramV2Resource();
        
        $this->assertInstanceOf(WorkoutProgramV2Resource::class, $resource);
        $this->assertEquals('Тренировки V2', $resource::getNavigationGroup());
        $this->assertEquals('Программы тренировок', $resource::getNavigationLabel());
    }

    /** @test */
    public function workout_exercise_v2_resource_can_be_instantiated()
    {
        $resource = new WorkoutExerciseV2Resource();
        
        $this->assertInstanceOf(WorkoutExerciseV2Resource::class, $resource);
        $this->assertEquals('Тренировки V2', $resource::getNavigationGroup());
        $this->assertEquals('Упражнения', $resource::getNavigationLabel());
    }

    /** @test */
    public function user_subscription_v2_resource_can_be_instantiated()
    {
        $resource = new UserSubscriptionV2Resource();
        
        $this->assertInstanceOf(UserSubscriptionV2Resource::class, $resource);
        $this->assertEquals('Тренировки V2', $resource::getNavigationGroup());
        $this->assertEquals('Подписки пользователей', $resource::getNavigationLabel());
    }

    /** @test */
    public function user_workout_progress_v2_resource_can_be_instantiated()
    {
        $resource = new UserWorkoutProgressV2Resource();
        
        $this->assertInstanceOf(UserWorkoutProgressV2Resource::class, $resource);
        $this->assertEquals('Тренировки V2', $resource::getNavigationGroup());
        $this->assertEquals('Прогресс тренировок', $resource::getNavigationLabel());
    }

    /** @test */
    public function all_resources_have_correct_navigation_group()
    {
        $resources = [
            WorkoutCategoryV2Resource::class,
            WorkoutProgramV2Resource::class,
            WorkoutExerciseV2Resource::class,
            UserSubscriptionV2Resource::class,
            UserWorkoutProgressV2Resource::class,
        ];

        foreach ($resources as $resourceClass) {
            $this->assertEquals('Тренировки V2', $resourceClass::getNavigationGroup());
        }
    }

    /** @test */
    public function all_resources_have_correct_model_class()
    {
        $resources = [
            WorkoutCategoryV2Resource::class,
            WorkoutProgramV2Resource::class,
            WorkoutExerciseV2Resource::class,
            UserSubscriptionV2Resource::class,
            UserWorkoutProgressV2Resource::class,
        ];

        foreach ($resources as $resourceClass) {
            $this->assertNotNull($resourceClass::getModel());
        }
    }

    /** @test */
    public function all_resources_have_correct_navigation_icon()
    {
        $resources = [
            WorkoutCategoryV2Resource::class,
            WorkoutProgramV2Resource::class,
            WorkoutExerciseV2Resource::class,
            UserSubscriptionV2Resource::class,
            UserWorkoutProgressV2Resource::class,
        ];

        foreach ($resources as $resourceClass) {
            $this->assertNotNull($resourceClass::getNavigationIcon());
        }
    }
}
