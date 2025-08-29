<?php

declare(strict_types=1);

namespace App\Services\V2;

use App\Models\WorkoutExerciseV2;
use App\Models\WorkoutProgramV2;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

/**
 * Сервис для работы с видео тренировок
 */
class WorkoutVideoService
{
    /**
     * Загрузить видео файл для программы тренировок
     */
    public function uploadProgramVideo(WorkoutProgramV2 $program, UploadedFile $videoFile): string
    {
        $filename = sprintf(
            'program_%d_%s.%s',
            $program->id,
            time(),
            $videoFile->getClientOriginalExtension()
        );
        
        $path = $videoFile->storeAs(
            'workout-videos/programs',
            $filename,
            'public'
        );
        
        return $path;
    }
    
    /**
     * Загрузить видео файл для упражнения
     */
    public function uploadExerciseVideo(WorkoutExerciseV2 $exercise, UploadedFile $videoFile): string
    {
        $filename = sprintf(
            'exercise_%d_%s.%s',
            $exercise->id,
            time(),
            $videoFile->getClientOriginalExtension()
        );
        
        $path = $videoFile->storeAs(
            'workout-videos/exercises',
            $filename,
            'public'
        );
        
        return $path;
    }
    
    /**
     * Удалить видео файл
     */
    public function deleteVideo(string $videoPath): bool
    {
        if (Storage::disk('public')->exists($videoPath)) {
            return Storage::disk('public')->delete($videoPath);
        }
        
        return false;
    }
    
    /**
     * Получить полный URL для видео
     */
    public function getVideoUrl(?string $videoPath): ?string
    {
        if (!$videoPath) {
            return null;
        }
        
        return Storage::disk('public')->url($videoPath);
    }
    
    /**
     * Проверить, является ли URL внешним видео
     */
    public function isExternalVideo(?string $url): bool
    {
        if (!$url) {
            return false;
        }
        
        // Проверяем, что это валидный URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Проверяем, что это не локальный файл
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';
        
        // Считаем внешними только известные платформы для видео
        $externalPlatforms = [
            'youtube.com',
            'youtu.be',
            'vimeo.com',
            'dailymotion.com',
            'twitch.tv'
        ];
        
        foreach ($externalPlatforms as $platform) {
            if (str_contains($host, $platform)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Получить тип видео (локальное или внешнее)
     */
    public function getVideoType(?string $videoUrl, ?string $videoFile): string
    {
        if ($videoFile) {
            return 'local';
        }
        
        if ($this->isExternalVideo($videoUrl)) {
            return 'external';
        }
        
        return 'none';
    }
    
    /**
     * Валидировать видео файл
     */
    public function validateVideoFile(UploadedFile $file): array
    {
        $errors = [];
        
        // Проверка размера (максимум 100 MB)
        $maxSize = 100 * 1024 * 1024; // 100 MB
        if ($file->getSize() > $maxSize) {
            $errors[] = 'Размер файла превышает 100 MB';
        }
        
        // Проверка расширения файла (более надежно, чем MIME тип)
        $allowedExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Неподдерживаемое расширение файла: ' . $extension;
        }
        
        return $errors;
    }
}
