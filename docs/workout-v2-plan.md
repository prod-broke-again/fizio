# План разработки Workout V2 - Система тренировок с подпиской

## Обзор проекта
Создание v2 версии системы тренировок с разделением на мужские/женские категории, системой подписки и админкой для управления контентом.

## Архитектурные принципы
- **SOLID принципы**: Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion
- **PSR-12**: Стандарты кодирования PHP
- **DRY**: Don't Repeat Yourself
- **KISS**: Keep It Simple, Stupid
- **Dependency Injection**: Внедрение зависимостей через контейнер Laravel
- **Domain-Driven Design**: Разделение на слои Domain/Application/Infrastructure

## Структура базы данных V2

### 1. Таблица `workout_categories_v2`
```sql
- id (uuid, primary)
- name (string) - название категории
- gender (enum: 'male', 'female') - пол
- slug (string, unique) - URL-friendly название
- description (text) - описание категории
- is_active (boolean) - активна ли категория
- sort_order (integer) - порядок сортировки
- created_at, updated_at
```

### 2. Таблица `workout_programs_v2`
```sql
- id (uuid, primary)
- category_id (uuid, foreign key) - связь с категорией
- name (string) - название программы
- slug (string, unique) - URL-friendly название
- description (text) - описание программы
- short_description (string) - краткое описание
- difficulty_level (enum: 'beginner', 'intermediate', 'advanced')
- duration_weeks (integer) - продолжительность в неделях
- calories_per_workout (integer) - калории за тренировку
- is_free (boolean) - бесплатная ли программа
- is_active (boolean) - активна ли программа
- sort_order (integer) - порядок сортировки
- created_at, updated_at
```

### 3. Таблица `workout_exercises_v2`
```sql
- id (uuid, primary)
- program_id (uuid, foreign key) - связь с программой
- name (string) - название упражнения
- description (text) - описание упражнения
- video_url (string) - ссылка на видео
- thumbnail_url (string) - превью изображение
- duration_seconds (integer) - длительность в секундах
- sets (integer) - количество подходов
- reps (integer) - количество повторений
- rest_seconds (integer) - отдых между подходами
- equipment_needed (json) - необходимое оборудование
- muscle_groups (json) - группы мышц
- sort_order (integer) - порядок в программе
- created_at, updated_at
```

### 4. Таблица `user_subscriptions_v2`
```sql
- id (uuid, primary)
- user_id (uuid, foreign key) - связь с пользователем
- subscription_type (enum: 'monthly', 'yearly', 'lifetime')
- status (enum: 'active', 'expired', 'cancelled')
- starts_at (timestamp) - начало подписки
- expires_at (timestamp) - окончание подписки
- created_at, updated_at
```

### 5. Таблица `user_workout_progress_v2`
```sql
- id (uuid, primary)
- user_id (uuid, foreign key) - связь с пользователем
- program_id (uuid, foreign key) - связь с программой
- exercise_id (uuid, foreign key) - связь с упражнением
- completed_at (timestamp) - время завершения
- duration_seconds (integer) - фактическая длительность
- notes (text) - заметки пользователя
- created_at, updated_at
```

## Модели V2

### 1. WorkoutCategoryV2
- Отношения: hasMany(WorkoutProgramV2)
- Enum для gender
- Scope для активных категорий

### 2. WorkoutProgramV2
- Отношения: belongsTo(WorkoutCategoryV2), hasMany(WorkoutExerciseV2)
- Enum для difficulty_level
- Scope для бесплатных/платных программ

### 3. WorkoutExerciseV2
- Отношения: belongsTo(WorkoutProgramV2)
- Cast для equipment_needed и muscle_groups как JSON

### 4. UserSubscriptionV2
- Отношения: belongsTo(User)
- Enum для subscription_type и status
- Методы для проверки активности подписки

### 5. UserWorkoutProgressV2
- Отношения: belongsTo(User), belongsTo(WorkoutProgramV2), belongsTo(WorkoutExerciseV2)

## API Endpoints V2

### 1. Категории тренировок
```
GET /api/v2/workout-categories
GET /api/v2/workout-categories/{slug}
```

### 2. Программы тренировок
```
GET /api/v2/workout-programs
GET /api/v2/workout-programs/{slug}
GET /api/v2/workout-programs/category/{categorySlug}
```

### 3. Упражнения
```
GET /api/v2/workout-exercises/{id}
GET /api/v2/workout-exercises/program/{programId}
```

### 4. Прогресс пользователя
```
GET /api/v2/user/workout-progress
POST /api/v2/user/workout-progress
PUT /api/v2/user/workout-progress/{id}
```

### 5. Подписки
```
GET /api/v2/user/subscription
POST /api/v2/user/subscription
```

## Filament Resources V2

### 1. WorkoutCategoryV2Resource
- CRUD операции для категорий
- Управление активностью и сортировкой
- Валидация уникальности slug

### 2. WorkoutProgramV2Resource
- CRUD операции для программ
- Связь с категориями
- Управление бесплатными/платными программами
- Загрузка изображений

### 3. WorkoutExerciseV2Resource
- CRUD операции для упражнений
- Связь с программами
- Загрузка видео и изображений
- JSON поля для оборудования и групп мышц

### 4. UserSubscriptionV2Resource
- Просмотр подписок пользователей
- Управление статусами подписок

## Сервисы V2

### 1. WorkoutServiceV2
- Бизнес-логика для тренировок
- Фильтрация по полу и категориям
- Проверка доступа к платному контенту

### 2. SubscriptionServiceV2
- Управление подписками
- Проверка активности подписки
- Расчет дат истечения

### 3. WorkoutProgressServiceV2
- Отслеживание прогресса пользователя
- Статистика и аналитика

## Валидация и авторизация

### 1. Form Requests
- WorkoutCategoryV2Request
- WorkoutProgramV2Request
- WorkoutExerciseV2Request
- UserSubscriptionV2Request

### 2. Policies
- WorkoutCategoryV2Policy
- WorkoutProgramV2Policy
- WorkoutExerciseV2Policy

### 3. Middleware
- CheckSubscription - проверка активной подписки для платного контента

## Тестирование

### 1. Unit Tests
- Модели и их отношения
- Сервисы и бизнес-логика
- Валидация и авторизация

### 2. Feature Tests
- API endpoints
- Filament ресурсы
- Интеграция с базой данных

### 3. Database Tests
- Миграции
- Сидеры
- Фабрики

## Этапы разработки

### Этап 1: База данных и модели (2-3 дня)
- [x] Создание миграций для всех таблиц V2
- [x] Создание моделей с отношениями
- [x] Создание фабрик и сидеров для тестирования
- [ ] Написание базовых тестов для моделей

### Этап 2: API и сервисы (3-4 дня)
- [ ] Создание API контроллеров V2
- [ ] Реализация сервисов
- [ ] Создание Form Requests и валидации
- [ ] Написание тестов для API

### Этап 3: Filament админка (2-3 дня)
- [ ] Создание Filament ресурсов V2
- [ ] Настройка форм и таблиц
- [ ] Загрузка файлов (видео, изображения)
- [ ] Тестирование админки

### Этап 4: Интеграция и тестирование (2-3 дня)
- [ ] Интеграционное тестирование
- [ ] Настройка middleware для подписок
- [ ] Документация API
- [ ] Финальное тестирование

## Технические требования

### 1. PHP 8.1+
- Использование современных фич PHP
- Строгая типизация
- Enum классы

### 2. Laravel 10+
- Современные возможности фреймворка
- Eloquent ORM с UUID
- API Resources для сериализации

### 3. Filament 3
- Современный интерфейс админки
- Загрузка файлов
- Связи между ресурсами

### 4. База данных
- UUID как первичные ключи
- JSON поля для гибкости
- Индексы для производительности

## Безопасность

### 1. CSRF защита
- Токены для всех форм
- Middleware для API

### 2. XSS защита
- Экранирование вывода
- Валидация входных данных

### 3. Авторизация
- Policies для каждого ресурса
- Проверка прав доступа

### 4. Валидация
- Строгая валидация всех входных данных
- Санитизация данных

## Производительность

### 1. Кеширование
- Кеширование категорий и программ
- Redis для сессий

### 2. Оптимизация запросов
- Eager loading для отношений
- Индексы в базе данных

### 3. Пагинация
- Пагинация для списков
- Ленивая загрузка

## Мониторинг и логирование

### 1. Логирование
- Логирование всех операций
- Отслеживание ошибок

### 2. Метрики
- Время ответа API
- Количество запросов
- Статистика использования

## Документация

### 1. API документация
- OpenAPI/Swagger спецификация
- Примеры запросов и ответов

### 2. Техническая документация
- Описание архитектуры
- Инструкции по развертыванию

### 3. Пользовательская документация
- Руководство по админке
- FAQ для пользователей
