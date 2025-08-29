<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\WorkoutExerciseV2;
use App\Models\WorkoutProgramV2;
use App\Services\V2\WorkoutVideoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkoutVideoServiceTest extends TestCase
{
    private WorkoutVideoService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new WorkoutVideoService();
        Storage::fake('public');
    }
    
    public function test_upload_program_video(): void
    {
        $program = WorkoutProgramV2::factory()->create();
        $videoFile = UploadedFile::fake()->create('video.mp4', 1024, 'video/mp4');
        
        $path = $this->service->uploadProgramVideo($program, $videoFile);
        
        $this->assertStringContainsString('workout-videos/programs', $path);
        $this->assertStringContainsString('program_' . $program->id, $path);
        Storage::disk('public')->assertExists($path);
    }
    
    public function test_upload_exercise_video(): void
    {
        $exercise = WorkoutExerciseV2::factory()->create();
        $videoFile = UploadedFile::fake()->create('video.mp4', 1024, 'video/mp4');
        
        $path = $this->service->uploadExerciseVideo($exercise, $videoFile);
        
        $this->assertStringContainsString('workout-videos/exercises', $path);
        $this->assertStringContainsString('exercise_' . $exercise->id, $path);
        Storage::disk('public')->assertExists($path);
    }
    
    public function test_delete_video(): void
    {
        $videoPath = 'workout-videos/test.mp4';
        Storage::disk('public')->put($videoPath, 'test content');
        
        $result = $this->service->deleteVideo($videoPath);
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($videoPath);
    }
    
    public function test_get_video_url(): void
    {
        $videoPath = 'workout-videos/test.mp4';
        
        $url = $this->service->getVideoUrl($videoPath);
        
        $this->assertStringContainsString('/storage/workout-videos/test.mp4', $url);
    }
    
    public function test_get_video_url_null(): void
    {
        $url = $this->service->getVideoUrl(null);
        
        $this->assertNull($url);
    }
    
    public function test_is_external_video(): void
    {
        $this->assertTrue($this->service->isExternalVideo('https://youtube.com/watch?v=123'));
        $this->assertTrue($this->service->isExternalVideo('https://vimeo.com/123'));
        $this->assertFalse($this->service->isExternalVideo('https://example.com/video.mp4'));
        $this->assertFalse($this->service->isExternalVideo(null));
        $this->assertFalse($this->service->isExternalVideo('not-a-url'));
    }
    
    public function test_get_video_type(): void
    {
        $this->assertEquals('local', $this->service->getVideoType(null, 'local/path.mp4'));
        $this->assertEquals('external', $this->service->getVideoType('https://youtube.com/watch?v=123', null));
        $this->assertEquals('none', $this->service->getVideoType(null, null));
    }
    
    public function test_validate_video_file(): void
    {
        // Создаем файлы с правильными расширениями
        $validFile = UploadedFile::fake()->create('video.mp4', 1024); // 1 KB
        $largeFile = UploadedFile::fake()->create('video.mp4', 1024);
        $invalidTypeFile = UploadedFile::fake()->create('video.txt', 1024);
        
        // Отладочная информация
        $this->assertEquals('mp4', $validFile->getClientOriginalExtension());
        $this->assertEquals('txt', $invalidTypeFile->getClientOriginalExtension());
        
        // Проверяем, что валидный файл проходит валидацию
        $validErrors = $this->service->validateVideoFile($validFile);
        $this->assertEmpty($validErrors, 'Валидный файл не должен иметь ошибок');
        
        // Проверяем, что файл неправильного типа не проходит валидацию
        $invalidErrors = $this->service->validateVideoFile($invalidTypeFile);
        $this->assertNotEmpty($invalidErrors, 'Файл неправильного типа должен иметь ошибки');
        $this->assertStringContainsString('txt', $invalidErrors[0]);
    }
}
