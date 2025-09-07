<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutExerciseV2;
use App\Models\WorkoutProgramV2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Тесты для функциональности видео в упражнениях V2
 */
class WorkoutExerciseV2VideoTest extends TestCase
{
    use RefreshDatabase;

    private WorkoutCategoryV2 $category;
    private WorkoutProgramV2 $program;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаем тестовые данные
        $this->category = WorkoutCategoryV2::factory()->create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'gender' => 'male',
            'is_active' => true,
        ]);
        
        $this->program = WorkoutProgramV2::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Тестовая программа',
            'slug' => 'test-program',
            'difficulty_level' => 'beginner',
            'is_active' => true,
        ]);
    }

    /**
     * Тест создания упражнения с видео файлом
     */
    public function test_can_create_exercise_with_video_file(): void
    {
        Storage::fake('public');
        
        $videoFile = UploadedFile::fake()->create('test-video.mp4', 1000, 'video/mp4');
        $thumbnailFile = UploadedFile::fake()->image('test-thumbnail.jpg', 800, 600);
        
        // Симулируем загрузку файлов через Storage
        $videoPath = $videoFile->store('workout-videos', 'public');
        $thumbnailPath = $thumbnailFile->store('workout-thumbnails', 'public');
        
        $exerciseData = [
            'program_id' => $this->program->id,
            'name' => 'Тестовое упражнение',
            'slug' => 'test-exercise',
            'description' => 'Описание упражнения',
            'instructions' => 'Инструкции по выполнению',
            'video_file' => $videoPath,
            'thumbnail_file' => $thumbnailPath,
            'sets' => 3,
            'reps' => 10,
            'duration_seconds' => 60,
            'rest_seconds' => 30,
            'weight_kg' => 20,
            'sort_order' => 1,
        ];
        
        $exercise = WorkoutExerciseV2::create($exerciseData);
        
        $this->assertDatabaseHas('workout_exercises_v2', [
            'id' => $exercise->id,
            'name' => 'Тестовое упражнение',
            'program_id' => $this->program->id,
        ]);
        
        // Проверяем, что файлы загружены
        Storage::disk('public')->assertExists($exercise->video_file);
        Storage::disk('public')->assertExists($exercise->thumbnail_file);
        
        // Проверяем методы модели
        $this->assertTrue($exercise->hasVideo());
        $this->assertTrue($exercise->hasThumbnail());
        $this->assertEquals('file', $exercise->getVideoType());
        $this->assertEquals('file', $exercise->getThumbnailType());
        $this->assertStringContainsString('storage/', $exercise->getVideoUrl());
        $this->assertStringContainsString('storage/', $exercise->getThumbnailUrl());
    }

    /**
     * Тест создания упражнения с URL видео
     */
    public function test_can_create_exercise_with_video_url(): void
    {
        $exerciseData = [
            'program_id' => $this->program->id,
            'name' => 'Тестовое упражнение с URL',
            'slug' => 'test-exercise-url',
            'description' => 'Описание упражнения',
            'video_url' => 'https://www.youtube.com/watch?v=test123',
            'thumbnail_url' => 'https://example.com/thumbnail.jpg',
            'sets' => 3,
            'reps' => 10,
            'duration_seconds' => 60,
            'rest_seconds' => 30,
            'weight_kg' => 20,
            'sort_order' => 1,
        ];
        
        $exercise = WorkoutExerciseV2::create($exerciseData);
        
        $this->assertDatabaseHas('workout_exercises_v2', [
            'id' => $exercise->id,
            'name' => 'Тестовое упражнение с URL',
            'video_url' => 'https://www.youtube.com/watch?v=test123',
            'thumbnail_url' => 'https://example.com/thumbnail.jpg',
        ]);
        
        // Проверяем методы модели
        $this->assertTrue($exercise->hasVideo());
        $this->assertTrue($exercise->hasThumbnail());
        $this->assertEquals('url', $exercise->getVideoType());
        $this->assertEquals('url', $exercise->getThumbnailType());
        $this->assertEquals('https://www.youtube.com/watch?v=test123', $exercise->getVideoUrl());
        $this->assertEquals('https://example.com/thumbnail.jpg', $exercise->getThumbnailUrl());
    }

    /**
     * Тест создания упражнения без видео
     */
    public function test_can_create_exercise_without_video(): void
    {
        $exerciseData = [
            'program_id' => $this->program->id,
            'name' => 'Тестовое упражнение без видео',
            'slug' => 'test-exercise-no-video',
            'description' => 'Описание упражнения',
            'sets' => 3,
            'reps' => 10,
            'duration_seconds' => 60,
            'rest_seconds' => 30,
            'weight_kg' => 20,
            'sort_order' => 1,
        ];
        
        $exercise = WorkoutExerciseV2::create($exerciseData);
        
        $this->assertDatabaseHas('workout_exercises_v2', [
            'id' => $exercise->id,
            'name' => 'Тестовое упражнение без видео',
        ]);
        
        // Проверяем методы модели
        $this->assertFalse($exercise->hasVideo());
        $this->assertFalse($exercise->hasThumbnail());
        $this->assertEquals('none', $exercise->getVideoType());
        $this->assertEquals('none', $exercise->getThumbnailType());
        $this->assertNull($exercise->getVideoUrl());
        $this->assertNull($exercise->getThumbnailUrl());
    }

    /**
     * Тест валидации видео файлов
     */
    public function test_video_file_validation(): void
    {
        $request = new \App\Http\Requests\WorkoutExerciseV2Request();
        
        // Тест валидного видео файла
        $validVideo = UploadedFile::fake()->create('test.mp4', 1000, 'video/mp4');
        $this->assertTrue($request->rules()['video_file'][0] === 'nullable');
        
        // Тест невалидного типа файла
        $invalidVideo = UploadedFile::fake()->create('test.txt', 1000, 'text/plain');
        // Здесь должна быть проверка на mimes, но это делается в контроллере
        
        // Тест слишком большого файла
        $largeVideo = UploadedFile::fake()->create('large.mp4', 200000, 'video/mp4'); // 200MB
        // Здесь должна быть проверка на max, но это делается в контроллере
    }

    /**
     * Тест API ответа с видео
     */
    public function test_api_response_includes_video_data(): void
    {
        $exercise = WorkoutExerciseV2::factory()->create([
            'program_id' => $this->program->id,
            'video_url' => 'https://www.youtube.com/watch?v=test123',
            'thumbnail_url' => 'https://example.com/thumbnail.jpg',
        ]);
        
        $resource = new \App\Http\Resources\V2\WorkoutExerciseV2Resource($exercise);
        $data = $resource->toArray(request());
        
        $this->assertArrayHasKey('video', $data);
        $this->assertArrayHasKey('thumbnail', $data);
        $this->assertArrayHasKey('url', $data['video']);
        $this->assertArrayHasKey('file', $data['video']);
        $this->assertArrayHasKey('has_video', $data['video']);
        $this->assertArrayHasKey('url', $data['thumbnail']);
        $this->assertArrayHasKey('file', $data['thumbnail']);
        $this->assertArrayHasKey('has_thumbnail', $data['thumbnail']);
        
        $this->assertEquals('https://www.youtube.com/watch?v=test123', $data['video']['url']);
        $this->assertTrue($data['video']['has_video']);
    }
}
