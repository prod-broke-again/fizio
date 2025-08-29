# Интеграция видео в тренировки

## Обзор

Система поддерживает добавление видео для программ тренировок и отдельных упражнений. Видео могут быть как внешними (YouTube, Vimeo), так и локальными файлами.

## Возможности

### 1. Программы тренировок
- **URL видео**: Ссылки на внешние видео (YouTube, Vimeo)
- **URL превью**: Изображения для предварительного просмотра
- **Локальные файлы**: Загрузка видео файлов (до 100 MB)

### 2. Упражнения
- **URL видео**: Ссылки на внешние видео
- **URL превью**: Изображения для предварительного просмотра
- **Инструкции**: Текстовые инструкции по выполнению

## Структура базы данных

### Таблица `workout_programs_v2`
```sql
ALTER TABLE workout_programs_v2 ADD COLUMN video_url VARCHAR(255) NULL COMMENT 'URL видео программы тренировок';
ALTER TABLE workout_programs_v2 ADD COLUMN thumbnail_url VARCHAR(255) NULL COMMENT 'URL превью видео';
ALTER TABLE workout_programs_v2 ADD COLUMN video_file VARCHAR(255) NULL COMMENT 'Локальный файл видео';
```

### Таблица `workout_exercises_v2`
```sql
ALTER TABLE workout_exercises_v2 ADD COLUMN instructions TEXT NULL COMMENT 'Инструкции по выполнению упражнения';
-- Поля video_url и thumbnail_url уже существуют
```

## Использование в Filament

### Форма программы тренировок
```php
Forms\Components\Section::make('Видео')
    ->schema([
        Forms\Components\TextInput::make('video_url')
            ->label('URL видео')
            ->url()
            ->helperText('Ссылка на видео (YouTube, Vimeo и т.д.)')
            ->columnSpanFull(),
        
        Forms\Components\TextInput::make('thumbnail_url')
            ->label('URL превью')
            ->url()
            ->helperText('Ссылка на изображение превью видео')
            ->columnSpanFull(),
        
        Forms\Components\FileUpload::make('video_file')
            ->label('Видео файл')
            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/mov'])
            ->maxSize(1024 * 1024 * 100) // 100 MB
            ->directory('workout-videos')
            ->disk('public')
            ->helperText('Загрузите видео файл (максимум 100 MB)')
            ->columnSpanFull(),
    ])
    ->columns(1)
    ->collapsible()
    ->collapsed()
```

### Форма упражнения
```php
Forms\Components\Section::make('Видео')
    ->schema([
        Forms\Components\TextInput::make('video_url')
            ->label('URL видео')
            ->url()
            ->helperText('Ссылка на видео упражнения (YouTube, Vimeo и т.д.)')
            ->columnSpanFull(),
        
        Forms\Components\TextInput::make('thumbnail_url')
            ->label('URL превью')
            ->url()
            ->helperText('Ссылка на изображение превью видео')
            ->columnSpanFull(),
    ])
    ->columns(1)
    ->collapsible()
    ->collapsed()
```

## Таблицы

### Колонки для видео
```php
Tables\Columns\TextColumn::make('video_url')
    ->label('Видео')
    ->url()
    ->openUrlInNewTab()
    ->icon('heroicon-o-video-camera')
    ->color('info')
    ->toggleable()
    ->limit(30),

Tables\Columns\ImageColumn::make('thumbnail_url')
    ->label('Превью')
    ->circular()
    ->toggleable(),
```

## Сервис для работы с видео

### WorkoutVideoService
```php
use App\Services\V2\WorkoutVideoService;

$service = new WorkoutVideoService();

// Загрузка видео программы
$path = $service->uploadProgramVideo($program, $videoFile);

// Загрузка видео упражнения
$path = $service->uploadExerciseVideo($exercise, $videoFile);

// Удаление видео
$service->deleteVideo($videoPath);

// Получение URL
$url = $service->getVideoUrl($videoPath);

// Проверка типа видео
$type = $service->getVideoType($videoUrl, $videoFile);
```

## Поддерживаемые форматы

### Видео файлы
- MP4 (рекомендуется)
- WebM
- OGG
- AVI
- MOV

### Максимальный размер
- 100 MB для локальных файлов

### Внешние платформы
- YouTube
- Vimeo
- Другие с поддержкой прямых ссылок

## Хранение файлов

### Директории
- `storage/app/public/workout-videos/programs/` - видео программ
- `storage/app/public/workout-videos/exercises/` - видео упражнений

### Диск
- Используется публичный диск (`public`)
- Файлы доступны через `/storage/` URL

## Безопасность

### Валидация
- Проверка типа файла
- Проверка размера
- Валидация URL

### Ограничения
- Только авторизованные пользователи
- CSRF защита
- Фильтрация файлов

## Тестирование

### Unit тесты
```bash
php artisan test tests/Unit/WorkoutVideoServiceTest.php
```

### Тестирование загрузки
```bash
php artisan test --filter=WorkoutVideoServiceTest
```

## Миграции

### Запуск миграций
```bash
php artisan migrate
```

### Откат миграций
```bash
php artisan migrate:rollback
```

## Примеры использования

### 1. Добавление YouTube видео
```
video_url: https://www.youtube.com/watch?v=dQw4w9WgXcQ
thumbnail_url: https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg
```

### 2. Добавление Vimeo видео
```
video_url: https://vimeo.com/123456789
thumbnail_url: https://i.vimeocdn.com/video/123456789_640.jpg
```

### 3. Локальное видео
```
video_file: [загруженный файл]
```

## Компонент VideoPlayer

### Использование
```php
use App\Filament\Components\VideoPlayer;

VideoPlayer::make('video_preview')
    ->label('Предварительный просмотр')
    ->maxWidth('100%')
    ->controls()
    ->autoplay(false)
```

### Настройки
- `maxWidth()` - максимальная ширина
- `maxHeight()` - максимальная высота
- `autoplay()` - автовоспроизведение
- `loop()` - зацикливание
- `controls()` - элементы управления
- `muted()` - без звука

## API интеграция

### Получение видео
```php
// Программа тренировок
$program = WorkoutProgramV2::find(1);
$videoUrl = $program->video_url;
$thumbnailUrl = $program->thumbnail_url;
$videoFile = $program->video_file;

// Упражнение
$exercise = WorkoutExerciseV2::find(1);
$videoUrl = $exercise->video_url;
$thumbnailUrl = $exercise->thumbnail_url;
```

### Создание с видео
```php
$program = WorkoutProgramV2::create([
    'name' => 'Программа тренировок',
    'video_url' => 'https://youtube.com/watch?v=123',
    'thumbnail_url' => 'https://example.com/thumb.jpg',
    // ... другие поля
]);
```

## Мониторинг и логирование

### Логи загрузки
- Все операции с видео логируются
- Отслеживание ошибок загрузки
- Мониторинг использования диска

### Метрики
- Количество загруженных видео
- Размер используемого диска
- Популярность видео контента

## Будущие улучшения

### Планируемые функции
- Автоматическое создание превью
- Сжатие видео
- Адаптивные потоки
- Аналитика просмотров
- Интеграция с CDN

### Оптимизация
- Кеширование видео
- Ленивая загрузка
- Прогрессивная загрузка
- WebP превью
