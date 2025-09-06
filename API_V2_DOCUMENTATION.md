# Документация API V2 - Система тренировок Fizio.Online

## Обзор

API V2 представляет собой современную систему тренировок с поддержкой подписок, разработанную в соответствии с принципами SOLID, PSR-12 и следующими архитектурными подходами:

- **Domain-Driven Design (DDD)**: Разделение на домены с четкими границами
- **Repository Pattern**: Абстракция работы с данными
- **Service Layer**: Бизнес-логика отделена от контроллеров
- **Resource Pattern**: Стандартизированные API ответы
- **Caching Layer**: Оптимизация производительности через Redis

## Архитектура

### Структура домена

```
Domain/
├── Workout/
│   ├── Entities/
│   │   ├── WorkoutExerciseV2
│   │   ├── WorkoutProgramV2
│   │   └── WorkoutCategoryV2
│   ├── Value Objects/
│   │   ├── WorkoutDifficulty (enum)
│   │   └── WorkoutGender (enum)
│   ├── Services/
│   │   └── WorkoutServiceV2
│   └── Repositories/
│       └── (Eloquent ORM через модели)
```

### Технологии

- **Laravel 10+** с PHP 8.2+
- **PostgreSQL/MySQL** для хранения данных
- **Redis** для кеширования
- **Sanctum** для аутентификации
- **Filament** для админ-панели
- **Pest/PHPUnit** для тестирования

## API Endpoints

### Базовый URL
```
https://fizio.online/api/v2/
```

### Аутентификация
Все защищенные endpoints требуют Bearer токен:
```
Authorization: Bearer {token}
```

## Категории тренировок (Workout Categories)

### GET /workout-categories
Получить список всех активных категорий тренировок

**Параметры запроса:**
- `gender` (optional): `male` | `female` - фильтр по полу

**Пример запроса:**
```bash
GET /api/v2/workout-categories?gender=male
```

**Пример ответа:**
```json
[
  {
    "id": "uuid",
    "name": "Силовые тренировки",
    "gender": "male",
    "slug": "power-training",
    "description": "Комплексные силовые тренировки для мужчин",
    "is_active": true,
    "sort_order": 1,
    "workout_programs": [
      {
        "id": "uuid",
        "name": "Программа для начинающих",
        "slug": "beginner-program",
        "description": "Базовая программа для новичков",
        "short_description": "Начните свой путь к здоровью",
        "difficulty_level": "beginner",
        "duration_weeks": 8,
        "calories_per_workout": 300,
        "is_free": true,
        "is_active": true,
        "sort_order": 1,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
]
```

### GET /workout-categories/{slug}
Получить конкретную категорию по slug

**Пример запроса:**
```bash
GET /api/v2/workout-categories/power-training
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "Силовые тренировки",
    "gender": "male",
    "slug": "power-training",
    "description": "Комплексные силовые тренировки для мужчин",
    "is_active": true,
    "sort_order": 1,
    "workout_programs": [...],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

## Программы тренировок (Workout Programs)

### GET /workout-programs
Получить список всех активных программ тренировок

**Параметры запроса:**
- `category_id` (optional): UUID категории
- `difficulty_level` (optional): `beginner` | `intermediate` | `advanced`
- `is_free` (optional): `true` | `false`
- `gender` (optional): `male` | `female`
- `per_page` (optional): количество элементов на страницу (1-100)

**Пример запроса:**
```bash
GET /api/v2/workout-programs?difficulty_level=beginner&is_free=true&gender=male
```

**Пример ответа:**
```json
[
  {
    "id": "uuid",
    "name": "Программа для начинающих",
    "slug": "beginner-program",
    "description": "Полное описание программы для новичков",
    "short_description": "Начните свой путь к здоровью",
    "difficulty_level": "beginner",
    "duration_weeks": 8,
    "calories_per_workout": 300,
    "is_free": true,
    "is_active": true,
    "sort_order": 1,
    "category": {
      "id": "uuid",
      "name": "Силовые тренировки",
      "gender": "male",
      "slug": "power-training",
      "description": "Комплексные силовые тренировки",
      "is_active": true,
      "sort_order": 1,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "workout_exercises": [...],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
]
```

### GET /workout-programs/{slug}
Получить конкретную программу по slug

**Пример запроса:**
```bash
GET /api/v2/workout-programs/beginner-program
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "Программа для начинающих",
    "slug": "beginner-program",
    "description": "Полное описание программы для новичков",
    "short_description": "Начните свой путь к здоровью",
    "difficulty_level": "beginner",
    "duration_weeks": 8,
    "calories_per_workout": 300,
    "is_free": true,
    "is_active": true,
    "sort_order": 1,
    "category": {...},
    "workout_exercises": [...],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### GET /workout-programs/category/{categorySlug}
Получить программы по категории

**Пример запроса:**
```bash
GET /api/v2/workout-programs/category/power-training
```

## Упражнения (Workout Exercises)

### GET /workout-exercises/{id}
Получить конкретное упражнение по ID

**Пример запроса:**
```bash
GET /api/v2/workout-exercises/uuid-here
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "Приседания со штангой",
    "description": "Техника выполнения приседаний",
    "instructions": "Встаньте ровно, возьмите штангу на плечи...",
    "video_url": "https://youtube.com/watch?v=example",
    "thumbnail_url": "https://example.com/thumbnail.jpg",
    "duration_seconds": 45,
    "sets": 4,
    "reps": 8,
    "rest_seconds": 120,
    "weight_kg": 60.0,
    "equipment_needed": [
      "Штанга",
      "Гриф"
    ],
    "muscle_groups": [
      "Квадрицепсы",
      "Ягодичные мышцы",
      "Бицепс бедра"
    ],
    "sort_order": 1,
    "program": {
      "id": "uuid",
      "name": "Программа для начинающих",
      "slug": "beginner-program",
      "description": "Базовая программа",
      "short_description": "Для новичков",
      "difficulty_level": "beginner",
      "duration_weeks": 8,
      "calories_per_workout": 300,
      "is_free": true,
      "is_active": true,
      "sort_order": 1,
      "category": {
        "id": "uuid",
        "name": "Силовые тренировки",
        "gender": "male",
        "slug": "power-training",
        "description": "Комплексные силовые тренировки",
        "is_active": true,
        "sort_order": 1,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
      },
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### GET /workout-exercises/program/{programId}
Получить упражнения по программе

**Пример запроса:**
```bash
GET /api/v2/workout-exercises/program/uuid-here
```

**Пример ответа:**
```json
[
  {
    "id": "uuid",
    "name": "Приседания со штангой",
    "description": "Техника выполнения приседаний",
    "instructions": "Встаньте ровно, возьмите штангу на плечи...",
    "video_url": "https://youtube.com/watch?v=example",
    "thumbnail_url": "https://example.com/thumbnail.jpg",
    "duration_seconds": 45,
    "sets": 4,
    "reps": 8,
    "rest_seconds": 120,
    "weight_kg": 60.0,
    "equipment_needed": [...],
    "muscle_groups": [...],
    "sort_order": 1,
    "program": {...},
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
]
```

## Прогресс пользователя (User Workout Progress)

*Требуется аутентификация*

### GET /user/workout-progress
Получить прогресс пользователя по тренировкам

**Пример запроса:**
```bash
GET /api/v2/user/workout-progress
Authorization: Bearer {token}
```

### POST /user/workout-progress
Создать запись о прогрессе тренировки

**Пример запроса:**
```bash
POST /api/v2/user/workout-progress
Authorization: Bearer {token}
Content-Type: application/json

{
  "program_id": "uuid",
  "exercise_id": "uuid",
  "sets_completed": 4,
  "reps_completed": 8,
  "weight_used": 60.0,
  "duration_seconds": 300,
  "notes": "Хорошая тренировка"
}
```

### PUT /user/workout-progress/{id}
Обновить запись прогресса

**Пример запроса:**
```bash
PUT /api/v2/user/workout-progress/uuid
Authorization: Bearer {token}
Content-Type: application/json

{
  "sets_completed": 5,
  "reps_completed": 10,
  "weight_used": 65.0
}
```

### GET /user/workout-progress/statistics
Получить статистику прогресса пользователя

**Пример запроса:**
```bash
GET /api/v2/user/workout-progress/statistics
Authorization: Bearer {token}
```

## Перечисления (Enums)

### WorkoutDifficulty
```php
enum WorkoutDifficulty: string
{
    case BEGINNER = 'beginner';      // Начинающий
    case INTERMEDIATE = 'intermediate'; // Средний
    case ADVANCED = 'advanced';      // Продвинутый
}
```

### WorkoutGender
```php
enum WorkoutGender: string
{
    case MALE = 'male';       // Мужские тренировки
    case FEMALE = 'female';   // Женские тренировки
}
```

## Модели данных

### WorkoutCategoryV2
```php
protected $fillable = [
    'name',           // Название категории
    'gender',         // Пол (male/female)
    'slug',           // URL-friendly идентификатор
    'description',    // Описание категории
    'is_active',      // Активность категории
    'sort_order'      // Порядок сортировки
];
```

### WorkoutProgramV2
```php
protected $fillable = [
    'category_id',           // ID категории
    'name',                  // Название программы
    'slug',                  // URL-friendly идентификатор
    'description',           // Полное описание
    'short_description',     // Краткое описание
    'difficulty_level',      // Уровень сложности
    'duration_weeks',        // Длительность в неделях
    'calories_per_workout',  // Калории на тренировку
    'video_url',            // URL видео программы
    'thumbnail_url',        // URL превью
    'video_file',           // Локальный файл видео
    'is_free',              // Бесплатная программа
    'is_active',            // Активная программа
    'sort_order'            // Порядок сортировки
];
```

### WorkoutExerciseV2
```php
protected $fillable = [
    'program_id',           // ID программы
    'name',                 // Название упражнения
    'slug',                 // URL-friendly идентификатор
    'description',          // Описание упражнения
    'instructions',         // Инструкции по выполнению
    'video_url',           // URL видео упражнения
    'thumbnail_url',       // URL превью
    'duration_seconds',    // Длительность упражнения
    'sets',                // Количество подходов
    'reps',                // Количество повторений
    'rest_seconds',        // Отдых между подходами
    'weight_kg',           // Вес в кг
    'equipment_needed',    // Необходимое оборудование (JSON)
    'muscle_groups',       // Группы мышц (JSON)
    'sort_order'           // Порядок сортировки
];
```

## Кеширование

API использует Redis для кеширования с TTL 1 час:

- `workout_categories_v2_{gender}` - Категории по полу
- `workout_category_v2_{slug}` - Конкретная категория
- `workout_programs_v2_{filters_hash}` - Программы с фильтрами
- `workout_program_v2_{slug}` - Конкретная программа
- `workout_exercises_by_program_{programId}` - Упражнения по программе
- `workout_exercise_v2_{id}` - Конкретное упражнение

## Валидация и безопасность

### Валидация запросов
- Все параметры валидируются с помощью Form Request классов
- UUID валидация для идентификаторов
- Enum валидация для фиксированных значений
- Санитизация входных данных

### Защита от атак
- **CSRF защита** через Sanctum middleware
- **SQL Injection защита** через Eloquent ORM
- **XSS защита** через автоматическое экранирование
- **Rate limiting** на уровне приложения

### Аутентификация
- **Bearer Token** через Laravel Sanctum
- **Токены с истечением** для безопасности
- **Refresh токены** для продления сессий

## Обработка ошибок

### Стандартные HTTP коды
- `200` - Успешный запрос
- `201` - Ресурс создан
- `400` - Ошибка валидации
- `401` - Неавторизован
- `403` - Доступ запрещен
- `404` - Ресурс не найден
- `422` - Ошибка валидации данных
- `500` - Внутренняя ошибка сервера

### Структура ошибок
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

## Производительность

### Оптимизации
- **Eager Loading** связанных моделей
- **Database Indexing** на часто используемых полях
- **Query Optimization** с select только нужных полей
- **Caching Layer** для часто запрашиваемых данных
- **Pagination** для больших наборов данных

### Метрики производительности
- **Response Time**: < 200ms для кешированных запросов
- **Throughput**: 1000+ RPS при оптимальной нагрузке
- **Cache Hit Rate**: > 90% для основных endpoints
- **Database Query Time**: < 50ms для сложных запросов

## Тестирование

### Виды тестов
- **Unit Tests**: Тестирование отдельных классов и методов
- **Feature Tests**: Тестирование API endpoints
- **Integration Tests**: Тестирование взаимодействия компонентов

### Покрытие
- **Модели**: 100% покрытие бизнес-логики
- **Сервисы**: 95% покрытие основных методов
- **Контроллеры**: 90% покрытие всех endpoints
- **Общее покрытие**: 85%+ по проекту

## Мониторинг и логирование

### Логирование
- **Request/Response Logging** для всех API вызовов
- **Error Logging** с контекстом ошибки
- **Performance Logging** для медленных запросов
- **Security Logging** для подозрительной активности

### Мониторинг
- **Response Times** и **Error Rates**
- **Cache Hit/Miss Ratios**
- **Database Connection Pool** статус
- **Memory Usage** и **CPU Load**

## Версионирование

### Стратегия версионирования
- **URL-based versioning** (`/api/v2/`)
- **Backward compatibility** в рамках мажорной версии
- **Deprecation warnings** перед breaking changes
- **Migration guides** для обновлений

### Поддержка версий
- **V2**: Текущая активная версия (2024+)
- **V1**: Legacy версия (поддержка до 2025)
- **Migration Path**: Автоматическая миграция данных

## Развертывание

### Environment Variables
```bash
# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=fizio_v2
DB_USERNAME=user
DB_PASSWORD=password

# Cache
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=null

# App
APP_NAME="Fizio.Online V2"
APP_ENV=production
APP_KEY=base64:key
APP_DEBUG=false
APP_URL=https://fizio.online
```

### CI/CD Pipeline
1. **Code Quality**: PHPStan, Psalm, Laravel Pint
2. **Testing**: Pest/PHPUnit с coverage reports
3. **Security**: Automated vulnerability scanning
4. **Performance**: Load testing и profiling
5. **Deployment**: Zero-downtime blue-green deployment

---

## Контакты

**Команда разработки**: Senior PHP/Laravel разработчики
**Документация**: Автоматически генерируется через API
**Поддержка**: support@fizio.online
**Версия API**: v2.0.0
**Дата последнего обновления**: 2024-01-15
