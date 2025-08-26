# API V2 - Система тренировок с подпиской

## Обзор

API V2 предоставляет современный интерфейс для работы с системой тренировок, включая категории, программы, упражнения, прогресс пользователей и управление подписками.

## Базовый URL

```
https://your-domain.com/api/v2
```

## Аутентификация

API использует Laravel Sanctum для аутентификации. Для защищенных маршрутов необходимо передавать токен в заголовке:

```
Authorization: Bearer {your-token}
```

## Endpoints

### 1. Категории тренировок

#### Получить все категории
```http
GET /api/v2/workout-categories
```

**Query параметры:**
- `gender` (опционально) - фильтр по полу: `male`, `female`

**Пример ответа:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Силовые тренировки для мужчин",
      "gender": "male",
      "slug": "strength-training-male",
      "description": "Программы для развития силы и мышечной массы",
      "is_active": true,
      "sort_order": 1,
      "workout_programs": [...],
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

#### Получить конкретную категорию
```http
GET /api/v2/workout-categories/{slug}
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Силовые тренировки для мужчин",
    "gender": "male",
    "slug": "strength-training-male",
    "description": "Программы для развития силы и мышечной массы",
    "is_active": true,
    "sort_order": 1,
    "workout_programs": [
      {
        "id": 1,
        "name": "Начинающий уровень",
        "slug": "beginner-level",
        "description": "Базовая программа для новичков",
        "difficulty_level": "beginner",
        "is_free": true,
        "is_active": true
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### 2. Программы тренировок

#### Получить все программы
```http
GET /api/v2/workout-programs
```

**Query параметры:**
- `category_id` (опционально) - ID категории
- `difficulty_level` (опционально) - уровень сложности: `beginner`, `intermediate`, `advanced`
- `is_free` (опционально) - бесплатные программы: `true`, `false`
- `gender` (опционально) - фильтр по полу: `male`, `female`
- `per_page` (опционально) - количество на странице (1-100)

**Пример ответа:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Начинающий уровень",
      "slug": "beginner-level",
      "description": "Базовая программа для новичков",
      "short_description": "Идеально для начинающих",
      "difficulty_level": "beginner",
      "duration_weeks": 4,
      "calories_per_workout": 200,
      "is_free": true,
      "is_active": true,
      "sort_order": 1,
      "category": {...},
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

#### Получить конкретную программу
```http
GET /api/v2/workout-programs/{slug}
```

#### Получить программы по категории
```http
GET /api/v2/workout-programs/category/{categorySlug}
```

### 3. Упражнения

#### Получить конкретное упражнение
```http
GET /api/v2/workout-exercises/{id}
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Приседания",
    "description": "Базовое упражнение для ног",
    "video_url": "https://example.com/video.mp4",
    "thumbnail_url": "https://example.com/thumb.jpg",
    "duration_seconds": 60,
    "sets": 3,
    "reps": 12,
    "rest_seconds": 90,
    "equipment_needed": ["гантели", "коврик"],
    "muscle_groups": ["квадрицепсы", "ягодицы"],
    "sort_order": 1,
    "program": {...},
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### Получить упражнения по программе
```http
GET /api/v2/workout-exercises/program/{programId}
```

### 4. Прогресс пользователя (требует аутентификации)

#### Получить прогресс
```http
GET /api/v2/user/workout-progress
```

#### Сохранить прогресс
```http
POST /api/v2/user/workout-progress
```

**Тело запроса:**
```json
{
  "program_id": 1,
  "exercise_id": 1,
  "duration_seconds": 300,
  "notes": "Отличная тренировка!"
}
```

**Валидация:**
- `program_id` - обязательное, должно существовать в таблице `workout_programs_v2`
- `exercise_id` - обязательное, должно существовать в таблице `workout_exercises_v2`
- `completed_at` - опциональное, дата не может быть в будущем
- `duration_seconds` - опциональное, целое число 0-86400
- `notes` - опциональное, максимум 1000 символов

#### Обновить прогресс
```http
PUT /api/v2/user/workout-progress/{id}
```

**Тело запроса:**
```json
{
  "duration_seconds": 450,
  "notes": "Обновленные заметки"
}
```

#### Получить статистику
```http
GET /api/v2/user/workout-progress/statistics
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "total_workouts": 15,
    "total_duration_minutes": 450.5,
    "unique_programs": 3,
    "average_duration_minutes": 30.0,
    "weekly_stats": {
      "Понедельник": 3,
      "Вторник": 2,
      "Среда": 4,
      "Четверг": 1,
      "Пятница": 3,
      "Суббота": 2,
      "Воскресенье": 0
    },
    "program_stats": [
      {
        "program_name": "Начинающий уровень",
        "category_name": "Силовые тренировки",
        "workout_count": 8,
        "total_duration_minutes": 240.0
      }
    ],
    "current_streak": 5
  }
}
```

### 5. Подписки (требует аутентификации)

#### Получить текущую подписку
```http
GET /api/v2/user/subscription
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "subscription_type": "monthly",
    "status": "active",
    "starts_at": "2024-01-01T00:00:00.000000Z",
    "expires_at": "2024-02-01T00:00:00.000000Z",
    "days_remaining": 15,
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

#### Создать подписку
```http
POST /api/v2/user/subscription
```

**Тело запроса:**
```json
{
  "subscription_type": "monthly"
}
```

**Доступные типы:**
- `monthly` - месячная подписка
- `yearly` - годовая подписка
- `lifetime` - пожизненная подписка

#### Отменить подписку
```http
DELETE /api/v2/user/subscription/cancel
```

#### Получить статус подписки
```http
GET /api/v2/user/subscription/status
```

**Пример ответа:**
```json
{
  "success": true,
  "data": {
    "has_active_subscription": true,
    "subscription_type": "monthly",
    "expires_at": "2024-02-01T00:00:00.000000Z",
    "days_remaining": 15,
    "is_expired": false
  }
}
```

## Коды ответов

- `200` - Успешный запрос
- `201` - Ресурс создан
- `400` - Ошибка в запросе
- `401` - Не авторизован
- `403` - Доступ запрещен
- `404` - Ресурс не найден
- `422` - Ошибка валидации

## Обработка ошибок

Все ошибки возвращаются в следующем формате:

```json
{
  "success": false,
  "message": "Описание ошибки",
  "errors": {
    "field": ["Сообщение об ошибке"]
  }
}
```

## Кеширование

API использует кеширование для улучшения производительности:
- Категории и программы кешируются на 1 час
- Кеш автоматически очищается при обновлении данных
- Кеш учитывает параметры фильтрации

## Ограничения

- Максимум 100 записей на страницу
- Максимум 1000 символов в заметках
- Максимум 24 часа для длительности тренировки
- Только один активный тип подписки на пользователя

## Примеры использования

### Получение программ для мужчин начального уровня

```bash
curl -X GET "https://your-domain.com/api/v2/workout-programs?gender=male&difficulty_level=beginner" \
  -H "Accept: application/json"
```

### Сохранение прогресса тренировки

```bash
curl -X POST "https://your-domain.com/api/v2/user/workout-progress" \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "program_id": 1,
    "exercise_id": 1,
    "duration_seconds": 300,
    "notes": "Отличная тренировка!"
  }'
```

### Создание месячной подписки

```bash
curl -X POST "https://your-domain.com/api/v2/user/subscription" \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subscription_type": "monthly"
  }'
```

## Поддержка

Для получения поддержки по API обращайтесь к команде разработки или создавайте issue в репозитории проекта.
