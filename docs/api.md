# API Документация

## Аутентификация

### Регистрация
```http
POST /api/auth/register
```

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| name | string | Да | Имя пользователя |
| email | string | Да | Email пользователя |
| password | string | Да | Пароль (минимум 8 символов) |
| password_confirmation | string | Да | Подтверждение пароля |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Иван Иванов",
            "email": "ivan@example.com"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

### Вход
```http
POST /api/auth/login
```

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| email | string | Да | Email пользователя |
| password | string | Да | Пароль |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Иван Иванов",
            "email": "ivan@example.com"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

### Выход
```http
POST /api/auth/logout
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Пример ответа
```json
{
    "success": true,
    "message": "Успешный выход из системы"
}
```

### Обновление токена
```http
POST /api/auth/refresh
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

## Telegram Интеграция

### Авторизация через Telegram
```http
POST /api/auth/telegram
```

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| id | integer | Да | Telegram ID пользователя |
| first_name | string | Да | Имя пользователя в Telegram |
| username | string | Нет | Username в Telegram |
| photo_url | string | Нет | URL фото профиля |
| auth_date | integer | Да | Время авторизации (Unix timestamp) |
| hash | string | Да | Хеш для проверки подлинности |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Иван Иванов",
            "telegram_id": 123456789
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

### Привязка Telegram
```http
POST /api/auth/telegram/link
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| telegram_id | integer | Да | Telegram ID пользователя |

#### Пример ответа
```json
{
    "success": true,
    "message": "Telegram успешно привязан"
}
```

## Пользователь

### Профиль
```http
GET /api/user/profile
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Иван Иванов",
        "email": "ivan@example.com",
        "telegram_id": 123456789,
        "created_at": "2024-02-13T12:00:00Z",
        "updated_at": "2024-02-13T12:00:00Z"
    }
}
```

### Обновление профиля
```http
POST /api/user/profile
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| name | string | Нет | Новое имя пользователя |
| email | string | Нет | Новый email |
| current_password | string | Нет | Текущий пароль (если меняется email) |
| new_password | string | Нет | Новый пароль |
| new_password_confirmation | string | Нет | Подтверждение нового пароля |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Иван Иванов",
        "email": "ivan@example.com",
        "updated_at": "2024-02-13T12:00:00Z"
    }
}
```

### Цели фитнеса
```http
GET /api/user/fitness-goal
POST /api/user/fitness-goal
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Параметры запроса (POST)
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| goal_type | string | Да | Тип цели (weight_loss, muscle_gain, maintenance) |
| target_weight | float | Нет | Целевой вес (кг) |
| target_date | date | Нет | Дата достижения цели |
| weekly_workouts | integer | Да | Количество тренировок в неделю |
| daily_steps | integer | Да | Целевое количество шагов в день |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "goal_type": "weight_loss",
        "target_weight": 75.5,
        "target_date": "2024-12-31",
        "weekly_workouts": 4,
        "daily_steps": 10000
    }
}
```

### Пол
```http
GET /api/user/gender
```

## Прогресс и статистика

Здесь описываются эндпоинты для отслеживания прогресса пользователя, включая ежедневные показатели, измерения тела и цели.

### 1. Получение записей прогресса (Общий прогресс)
```http
GET /api/progress
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Параметры запроса (Query)
| Параметр | Тип    | Обязательный | Описание                                                                 |
|----------|--------|--------------|--------------------------------------------------------------------------|
| period   | string | Нет          | Период для выборки (`week`, `month`, `all`). По умолчанию `week`.        |
| endDate  | date   | Нет          | Конечная дата периода в формате `YYYY-MM-DD`. По умолчанию текущая дата. |

#### Пример ответа
```json
{
    "success": true,
    "data": [
        {
            "id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
            "userId": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
            "date": "2024-07-26",
            "calories": 1800,
            "steps": 7500,
            "workoutTime": 60,
            "waterIntake": 2.5,
            "weight": 70.5,
            "bodyFatPercentage": 15.2,
            "measurements": {
                "chest": 98.5,
                "waist": 75.0,
                "hips": 102.1
            },
            "photos": [
                "https://example.com/photo1_2024-07-26.jpg"
            ]
        }
        // ... другие записи
    ]
}
```
При отсутствии данных за период:
```json
{
    "success": true,
    "message": "Записи о прогрессе за указанный период не найдены.",
    "data": []
}
```

### 2. Получение прогресса по дате
```http
GET /api/progress/by-date
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Параметры запроса (Query)
| Параметр | Тип  | Обязательный | Описание                                   |
|----------|------|--------------|--------------------------------------------|
| date     | date | Да           | Дата для получения прогресса (YYYY-MM-DD). |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "b2c3d4e5-f6a7-8901-2345-678901bcdef0",
        "userId": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
        "date": "2024-07-25",
        "calories": 2100,
        "steps": 10500,
        "workoutTime": 90,
        "waterIntake": 3.0,
        "weight": 70.2,
        "bodyFatPercentage": 15.0,
        "measurements": {
            "waist": 74.5
        },
        "photos": []
    }
}
```
В случае отсутствия записи:
```json
{
    "success": false,
    "message": "Запись о прогрессе за указанную дату не найдена."
}
```

### 3. Добавление/Обновление измерений и дневных показателей
```http
POST /api/progress/measurements
PATCH /api/progress/update 
```
**Примечание:** Оба маршрута (`POST /api/progress/measurements` для добавления новых измерений и `PATCH /api/progress/update` для обновления существующей записи за день) используют один и тот же метод контроллера. `POST` обычно используется для создания новой записи с измерениями (если за эту дату еще нет записи), `PATCH` - для обновления полей существующей записи за указанную дату. Если запись на дату уже существует, оба метода обновят её.

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Параметры запроса (JSON Body)
| Параметр          | Тип    | Обязательный | Описание                                                                    |
|-------------------|--------|--------------|-----------------------------------------------------------------------------|
| date              | string | Да           | Дата записи (YYYY-MM-DD).                                                   |
| calories          | int    | Нет          | Потребленные калории.                                                       |
| steps             | int    | Нет          | Количество шагов.                                                           |
| workoutTime       | int    | Нет          | Время тренировки в минутах.                                                 |
| waterIntake       | float  | Нет          | Потребление воды в литрах.                                                  |
| weight            | float  | Нет          | Вес в кг.                                                                   |
| bodyFatPercentage | float  | Нет          | Процент жира в теле (0-100).                                                |
| measurements      | object | Нет          | Объемы тела (ключ-значение, например, `{"chest": 90, "waist": 70}`).        |
| photos            | array  | Нет          | Массив URL-адресов фотографий (например, `["http://example.com/photo.jpg"]`). |

#### Пример запроса
```json
{
    "date": "2024-07-26",
    "weight": 70.0,
    "bodyFatPercentage": 14.8,
    "measurements": {
        "waist": 74.0,
        "chest": 98.0
    },
    "photos": ["https://example.com/new_photo.jpg"]
}
```

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "c3d4e5f6-a7b8-9012-3456-789012cdef01",
        "userId": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
        "date": "2024-07-26",
        "calories": null, // если не было передано или уже было null
        "steps": null,
        "workoutTime": null,
        "waterIntake": null,
        "weight": 70.0,
        "bodyFatPercentage": 14.8,
        "measurements": {
            "waist": 74.0,
            "chest": 98.0
        },
        "photos": ["https://example.com/new_photo.jpg"]
    },
    "message": "Прогресс успешно сохранен"
}
```
В случае ошибки валидации:
```json
            {
    "success": false,
    "error": "Ошибка валидации",
    "messages": {
        "date": ["Поле date обязательно для заполнения."],
        "weight": ["Поле weight должно быть числом."]
    }
}
```

### 4. Добавление цели
```http
POST /api/progress/goals
```

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (JSON Body)
| Параметр     | Тип    | Обязательный | Описание                                                                 |
|--------------|--------|--------------|--------------------------------------------------------------------------|
| name         | string | Да           | Название цели (например, "Похудеть к лету").                             |
| type         | string | Да           | Тип цели (например, `weight`, `body_fat`, `run_distance`).                  |
| targetValue  | float  | Да           | Целевое значение.                                                        |
| currentValue | float  | Нет          | Текущее значение (если не указано, может браться из `startValue`).        |
| startValue   | float  | Нет          | Начальное значение при постановке цели.                                    |
| unit         | string | Да           | Единица измерения (например, `kg`, `%`, `km`).                             |
| targetDate   | string | Нет          | Целевая дата в формате `YYYY-MM-DD`.                                     |
| status       | string | Нет          | Статус цели (`active`, `completed`, `abandoned`). По умолчанию `active`. |

#### Пример запроса
```json
{
    "name": "Снизить вес до 65 кг",
    "type": "weight",
    "targetValue": 65.0,
    "startValue": 70.5,
    "currentValue": 70.1,
    "unit": "kg",
    "targetDate": "2024-12-31"
}
```

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "d4e5f6a7-b8c9-0123-4567-890123def012",
        "user_id": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
        "name": "Снизить вес до 65 кг",
        "type": "weight",
        "target_value": 65.0,
        "current_value": 70.1,
        "start_value": 70.5,
        "unit": "kg",
        "target_date": "2024-12-31",
        "status": "active",
        "notes": [],
        "created_at": "2024-07-26T10:00:00.000000Z",
        "updated_at": "2024-07-26T10:00:00.000000Z"
    },
    "message": "Цель успешно добавлена"
}
```

### 5. Обновление прогресса по цели
```http
POST /api/progress/goals/{goalId}/progress
```
Заменяет `{goalId}` на UUID цели.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (JSON Body)
| Параметр     | Тип    | Обязательный | Описание                                         |
|--------------|--------|--------------|--------------------------------------------------|
| currentValue | float  | Да           | Новое текущее значение цели.                     |
| date         | string | Нет          | Дата записи прогресса (YYYY-MM-DD). По умолчанию текущая. |
| noteText     | string | Нет          | Текстовая заметка к этому обновлению прогресса.  |

#### Пример запроса
```json
{
    "currentValue": 69.5,
    "date": "2024-08-01",
    "noteText": "Соблюдал диету всю неделю."
}
```

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "d4e5f6a7-b8c9-0123-4567-890123def012",
        "user_id": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
        "name": "Снизить вес до 65 кг",
        "type": "weight",
        "target_value": 65.0,
        "current_value": 69.5,
        "start_value": 70.5,
        "unit": "kg",
        "target_date": "2024-12-31",
        "status": "active",
        "notes": [
            {
                "date": "2024-08-01",
                "value": 69.5,
                "note": "Соблюдал диету всю неделю."
            }
        ],
        "created_at": "2024-07-26T10:00:00.000000Z",
        "updated_at": "2024-08-01T10:00:00.000000Z" 
    },
    "message": "Прогресс по цели успешно обновлен"
}
```

### 6. Получение статистики (В разработке)
```http
GET /api/progress/statistics
```
Отображает статистику прогресса пользователя.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Пример ответа (заглушка)
```json
{
    "success": true,
    "message": "Endpoint статистики находится в разработке.",
    "data": []
}
```

### 7. Получение достижений (В разработке)
```http
GET /api/progress/achievements
```
Отображает достижения пользователя.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Пример ответа (заглушка)
```json
{
    "success": true,
    "message": "Endpoint достижений находится в разработке.",
    "data": []
}
```

## Тренировки

Раздел API для управления тренировками пользователя.

### 1. План тренировок на неделю
```http
GET /api/workouts/plan
```

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Пример ответа
```json
{
    "success": true,
    "data": [
        {
            "id": "e1f2a3b4-c5d6-7890-1234-567890abcdef",
            "name": "Утренняя пробежка",
            "type": "cardio",
            "exercises": [
                {"exerciseId": "running_01", "sets": [{"duration": 30, "distance": 5}], "order": 0, "restTime": 0}
            ],
            "duration": 30, // Общая продолжительность в минутах
                "difficulty": "intermediate",
            "date": "2024-07-29",
            "completed": false,
            "caloriesBurned": null
        }
        // ... другие тренировки плана
    ],
    "message": "План тренировок успешно получен"
}
```

### 2. Список всех тренировок пользователя
```http
GET /api/workouts
```

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (Query)
| Параметр | Тип     | Обязательный | Описание                                     |
|----------|---------|--------------|----------------------------------------------|
| page     | integer | Нет          | Номер страницы (по умолчанию 1).             |
| perPage  | integer | Нет          | Количество элементов на странице (по умолчанию 20, макс 50). |
| type     | string  | Нет          | Фильтр по типу тренировки (`strength`, `cardio`, `hiit`, `flexibility`). |
| difficulty | string | Нет        | Фильтр по сложности (`beginner`, `intermediate`, `advanced`). |
| dateFrom | date    | Нет          | Фильтр по дате начала (YYYY-MM-DD).          |
| dateTo   | date    | Нет          | Фильтр по дате окончания (YYYY-MM-DD).       |

#### Пример ответа (Пагинированный)
```json
{
    "success": true,
            "current_page": 1,
    "data": [
        {
            "id": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
            "name": "Силовая тренировка на все тело",
            "type": "strength",
            "exercises": [
                {"exerciseId": "squats_01", "sets": [{"weight": 60, "reps": 12}, {"weight": 60, "reps": 12}], "order": 0, "restTime": 60},
                {"exerciseId": "bench_press_01", "sets": [{"weight": 50, "reps": 10}], "order": 1, "restTime": 60}
            ],
            "duration": 60,
            "difficulty": "intermediate",
            "date": "2024-07-28",
            "completed": true,
            "caloriesBurned": 350
        }
    ],
    "first_page_url": "/api/workouts?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "/api/workouts?page=3",
    "links": [
        // ... ссылки пагинации
    ],
    "next_page_url": "/api/workouts?page=2",
    "path": "/api/workouts",
    "per_page": 1,
    "prev_page_url": null,
    "to": 1,
    "total": 3
}
```

### 3. Добавление новой тренировки
```http
POST /api/workouts
```

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (JSON Body)
| Параметр          | Тип    | Обязательный | Описание                                                                                                |
|-------------------|--------|--------------|---------------------------------------------------------------------------------------------------------|
| name              | string | Да           | Название тренировки.                                                                                    |
| type              | string | Да           | Тип тренировки (`strength`, `cardio`, `hiit`, `flexibility`).                                            |
| exercises         | array  | Да           | Массив упражнений. Каждое упражнение - объект.                                                          |
| exercises[].exerciseId | string | Да   | Идентификатор упражнения (например, из справочника упражнений).                                            |
| exercises[].sets  | array  | Да           | Массив подходов. Каждый подход - объект с полями `weight`, `reps`, `duration`, `distance` (зависят от типа).|
| exercises[].order | int    | Да           | Порядковый номер упражнения в тренировке.                                                                 |
| exercises[].restTime| int  | Да           | Время отдыха после упражнения в секундах.                                                                |
| duration          | int    | Да           | Общая предполагаемая длительность тренировки в минутах (min: 1).                                          |
| difficulty        | string | Да           | Сложность (`beginner`, `intermediate`, `advanced`).                                                     |
| date              | string | Да           | Дата тренировки (YYYY-MM-DD).                                                                           |

#### Пример запроса
```json
{
    "name": "Вечерняя HIIT тренировка",
    "type": "hiit",
    "exercises": [
        {
            "exerciseId": "burpees_01", 
            "sets": [{"duration": 45}], 
            "order": 0, 
            "restTime": 15
        },
        {
            "exerciseId": "jump_squats_01", 
            "sets": [{"duration": 45}], 
            "order": 1, 
            "restTime": 15
        }
    ],
    "duration": 20,
    "difficulty": "advanced",
    "date": "2024-07-30"
}
```

#### Пример ответа (201 Created)
```json
{
    "success": true,
    "data": {
        "id": "a1b2c3d4-e5f6-1234-5678-90abcdef1234",
        "name": "Вечерняя HIIT тренировка",
        "type": "hiit",
        "exercises": [
            {"exerciseId": "burpees_01", "sets": [{"duration": 45}], "order": 0, "restTime": 15},
            {"exerciseId": "jump_squats_01", "sets": [{"duration": 45}], "order": 1, "restTime": 15}
        ],
        "duration": 20,
        "difficulty": "advanced",
        "date": "2024-07-30",
        "completed": false,
        "caloriesBurned": null
    },
    "message": "Тренировка успешно добавлена"
}
```

### 4. Получение информации о тренировке
```http
GET /api/workouts/{workoutId}
```
Заменяет `{workoutId}` на UUID тренировки.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
        "name": "Силовая тренировка на все тело",
        "type": "strength",
        "exercises": [
            {"exerciseId": "squats_01", "sets": [{"weight": 60, "reps": 12}, {"weight": 60, "reps": 12}], "order": 0, "restTime": 60},
            {"exerciseId": "bench_press_01", "sets": [{"weight": 50, "reps": 10}], "order": 1, "restTime": 60}
        ],
        "duration": 60,
                "difficulty": "intermediate",
        "date": "2024-07-28",
        "completed": true,
        "caloriesBurned": 350
    },
    "message": "Тренировка успешно получена"
}
```
В случае отсутствия тренировки (404 Not Found):
```json
{
    "success": false,
    "error": "Ресурс не найден",
    "message": "Тренировка с указанным ID не найдена"
}
```

### 5. Обновление существующей тренировки
```http
PUT /api/workouts/{workoutId}
```
Заменяет `{workoutId}` на UUID тренировки.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (JSON Body)
Аналогичны параметрам для добавления новой тренировки (см. пункт 3), но все поля необязательные. Передаются только те поля, которые нужно изменить.

#### Пример запроса (обновление названия и сложности)
```json
{
    "name": "Легкая силовая тренировка",
    "difficulty": "beginner"
}
```

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
        "name": "Легкая силовая тренировка",
        "type": "strength",
        "exercises": [
            // ... старые упражнения ...
        ],
        "duration": 60,
        "difficulty": "beginner",
        "date": "2024-07-28",
        "completed": true,
        "caloriesBurned": 350
    },
    "message": "Тренировка успешно обновлена"
}
```

### 6. Удаление тренировки
```http
DELETE /api/workouts/{workoutId}
```
Заменяет `{workoutId}` на UUID тренировки.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Пример ответа (200 OK или 204 No Content)
```json
{
    "success": true,
    "message": "Тренировка успешно удалена"
}
```

### 7. Отметка тренировки как выполненной/невыполненной
```http
PATCH /api/workouts/{workoutId}/complete
```
Заменяет `{workoutId}` на UUID тренировки.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (JSON Body)
| Параметр        | Тип     | Обязательный | Описание                                         |
|-----------------|---------|--------------|--------------------------------------------------|
| completed       | boolean | Да           | `true` для отметки как выполненной, `false` - нет. |
| caloriesBurned | integer | Нет          | Количество сожженных калорий (если `completed` = `true`). |

#### Пример запроса
```json
{
    "completed": true,
    "caloriesBurned": 400
}
```

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "e1f2a3b4-c5d6-7890-1234-567890abcdef",
        "name": "Утренняя пробежка",
        "type": "cardio",
        "exercises": [
           // ... 
        ],
        "duration": 30,
        "difficulty": "intermediate",
        "date": "2024-07-29",
        "completed": true,
        "caloriesBurned": 400
    },
    "message": "Статус выполнения тренировки успешно обновлен"
}
```

## Уведомления

### Список уведомлений
```http
GET /api/notifications
```

### Отметить как прочитанное
```http
POST /api/notifications/{id}/read
```

### Отметить все как прочитанные
```http
POST /api/notifications/read-all
```

## Питание

Раздел API для управления планом питания и приемами пищи пользователя.

### 1. План питания на неделю
```http
GET /api/nutrition/plan
```

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Пример ответа
```json
{
    "success": true,
    "data": [
        {
            "id": "d1e2f3a4-b5c6-7890-1234-567890abcdef",
            "userId": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
            "name": "Завтрак", // Например, "Завтрак", "Омлет с овощами"
            "type": "breakfast", // breakfast, lunch, dinner, snack
            "date": "2024-07-29",
            "calories": 450,
            "proteins": 25,
            "fats": 15,
            "carbs": 50,
            "items": [
                {"foodId": "fatsecret_123", "name": "Овсянка на молоке", "quantity": 1, "unit": "порция", "calories": 300},
                {"foodId": "local_fdc_456", "name": "Яблоко", "quantity": 1, "unit": "шт", "calories": 150}
            ],
            "completed": false
        }
        // ... другие приемы пищи в плане
    ],
    "message": "План питания успешно получен"
}
```

### 2. Получение приемов пищи за день
```http
GET /api/nutrition/daily
```

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (Query)
| Параметр | Тип  | Обязательный | Описание                                   |
|----------|------|--------------|--------------------------------------------|
| date     | date | Да           | Дата для получения данных (YYYY-MM-DD).    |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "date": "2024-07-26",
        "meals": [
            {
                "id": "c1b2a3d4-e5f6-7890-1234-567890abcde",
                "name": "Обед",
                "type": "lunch",
                "calories": 700,
                "proteins": 40,
                "fats": 25,
                "carbs": 80,
                "items": [
                     {"foodId": "fs_xyz", "name": "Куриная грудка гриль", "quantity": 150, "unit": "г"},
                     {"foodId": "fs_abc", "name": "Гречка отварная", "quantity": 100, "unit": "г"}
                ],
                "completed": true
            }
            // ... другие приемы пищи за день
        ],
        "summary": {
            "totalCalories": 1800,
            "totalProteins": 100,
            "totalFats": 60,
            "totalCarbs": 200
        }
    },
    "message": "Данные о питании за день успешно получены"
}
```

### 3. Добавление нового приема пищи
```http
POST /api/nutrition/meals
```

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (JSON Body)
| Параметр      | Тип    | Обязательный | Описание                                                                                                     |
|---------------|--------|--------------|--------------------------------------------------------------------------------------------------------------|
| name          | string | Да           | Название приема пищи (например, "Завтрак" или "Куриная грудка с рисом").                                  |
| type          | string | Да           | Тип приема пищи (`breakfast`, `lunch`, `dinner`, `snack`).                                                     |
| date          | string | Да           | Дата приема пищи (YYYY-MM-DD).                                                                                |
| calories      | int    | Да           | Общее количество калорий.                                                                                      |
| proteins      | int    | Да           | Общее количество белков (г).                                                                                 |
| fats          | int    | Да           | Общее количество жиров (г).                                                                                  |
| carbs         | int    | Да           | Общее количество углеводов (г).                                                                              |
| items         | array  | Да           | Массив продуктов/блюд в приеме пищи.                                                                         |
| items[].foodId| string | Да           | Идентификатор продукта/блюда (может быть ID из FatSecret, OpenFoodFacts или внутренний ID).                  |
| items[].name  | string | Да           | Название продукта/блюда.                                                                                     |
| items[].quantity| float| Да           | Количество.                                                                                                  |
| items[].unit  | string | Да           | Единица измерения (например, `г`, `мл`, `порция`, `шт`).                                                        |
| items[].calories| int  | Да           | Калорийность данного продукта/блюда в указанном количестве.                                                    |
| items[].proteins| int  | Нет          | Белки для данного продукта/блюда.                                                                              |
| items[].fats  | int    | Нет          | Жиры для данного продукта/блюда.                                                                               |
| items[].carbs | int    | Нет          | Углеводы для данного продукта/блюда.                                                                           |

#### Пример запроса
```json
{
    "name": "Полдник",
    "type": "snack",
    "date": "2024-07-29",
    "calories": 250,
    "proteins": 10,
    "fats": 8,
    "carbs": 35,
    "items": [
        {
            "foodId": "fs_fruit_yogurt",
            "name": "Йогурт фруктовый",
            "quantity": 1,
            "unit": "шт",
            "calories": 150
        },
        {
            "foodId": "fs_banana",
            "name": "Банан",
            "quantity": 1,
            "unit": "шт",
            "calories": 100
        }
    ]
}
```

#### Пример ответа (201 Created)
```json
{
    "success": true,
    "data": {
        "id": "a1b2c3d4-e5f6-0987-6543-210fedcba987",
        "userId": "f0e1d2c3-b4a5-6789-0123-456789abcdef",
        "name": "Полдник",
        "type": "snack",
        "date": "2024-07-29",
        "calories": 250,
        "proteins": 10,
        "fats": 8,
        "carbs": 35,
        "items": [
            {"foodId": "fs_fruit_yogurt", "name": "Йогурт фруктовый", "quantity": 1, "unit": "шт", "calories": 150},
            {"foodId": "fs_banana", "name": "Банан", "quantity": 1, "unit": "шт", "calories": 100}
        ],
        "completed": false
    },
    "message": "Прием пищи успешно добавлен"
}
```

### 4. Обновление существующего приема пищи
```http
PUT /api/nutrition/meals/{mealId}
```
Заменяет `{mealId}` на UUID приема пищи.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (JSON Body)
Аналогичны параметрам для добавления нового приема пищи (см. пункт 3). Все поля обязательны для PUT запроса, так как он предполагает полную замену ресурса.

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "a1b2c3d4-e5f6-0987-6543-210fedcba987",
        // ... все поля приема пищи с обновленными значениями
    },
    "message": "Прием пищи успешно обновлен"
}
```

### 5. Удаление приема пищи
```http
DELETE /api/nutrition/meals/{mealId}
```
Заменяет `{mealId}` на UUID приема пищи.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Пример ответа (200 OK или 204 No Content)
```json
            {
    "success": true,
    "message": "Прием пищи успешно удален"
}
```

### 6. Отметка приема пищи как выполненного/невыполненного
```http
PATCH /api/nutrition/meals/{mealId}/complete
```
Заменяет `{mealId}` на UUID приема пищи.

#### Заголовки
| Заголовок     | Значение        |
|---------------|-----------------|
| Authorization | Bearer {token}  |

#### Параметры запроса (JSON Body)
| Параметр  | Тип     | Обязательный | Описание                                         |
|-----------|---------|--------------|--------------------------------------------------|
| completed | boolean | Да           | `true` для отметки как выполненного, `false` - нет. |

#### Пример запроса
```json
{
    "completed": true
}
```

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "id": "a1b2c3d4-e5f6-0987-6543-210fedcba987",
        // ... остальные поля приема пищи
        "completed": true
    },
    "message": "Статус выполнения приема пищи успешно обновлен"
}
```

## Интеграции

### Apple Watch
```http
POST /api/integrations/apple-watch/connect
POST /api/integrations/apple-watch/sync
```

### HealthKit
```http
POST /api/healthkit/sync
```

#### Заголовки
| Заголовок | Значение |
|-----------|-----------|
| Authorization | Bearer {token} |

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| steps | integer | Да | Количество шагов |
| calories | integer | Да | Сожженные калории |
| distance | float | Да | Пройденное расстояние (км) |
| duration | integer | Да | Продолжительность активности (минуты) |
| timestamp | datetime | Да | Время синхронизации |

#### Пример ответа
```json
{
    "success": true,
    "data": {
        "daily_progress": 75,
        "calories": 1200,
        "steps": 8500,
        "workout_time": 45,
        "water_intake": 0
    }
}
```

## Чат с AI-ассистентом

### Отправка сообщения
```http
POST /api/chat/send
```

### История чата
```http
GET /api/chat/history
```

### Обработка голоса
```http
POST /api/chat/voice
```

### Очистка истории
```http
DELETE /api/chat/clear
```

## Продукты питания

Этот раздел описывает интеграцию с внешними API для получения информации о продуктах питания.

### FatSecret API

Подробное описание эндпоинтов FatSecret API доступно в отдельном документе: [FatSecret API Документация](./api/fatsecret.md).

Основные возможности включают:
- Поиск продуктов
- Получение детальной информации о продукте
- Автозаполнение при поиске
- Распознавание продуктов по фото
- Поиск брендов
- Получение категорий продуктов
- Поиск рецептов

### OpenFoodFacts API
```http
GET /api/openfoodfacts/products/search
GET /api/openfoodfacts/products/barcode/{barcode}
```

### Spoonacular API
```http
GET /api/spoonacular/products/search
GET /api/spoonacular/products/upc/{upc}
```

## Общие особенности

### Аутентификация
Все защищенные эндпоинты требуют Bearer токен в заголовке:
```
Authorization: Bearer <token>
```

### Формат ответа
Успешный ответ:
```json
{
    "success": true,
    "data": {
        // Данные ответа
    }
}
```

Ошибка:
```json
{
    "success": false,
    "error": "Описание ошибки",
    "message": "Детальное сообщение об ошибке"
}
```

### Коды ошибок
- 400: Неверный запрос
- 401: Не авторизован
- 403: Доступ запрещен
- 404: Не найдено
- 422: Ошибка валидации
- 500: Внутренняя ошибка сервера

### Ограничения
- Все API требуют авторизации (кроме публичных эндпоинтов)
- Максимальный размер запроса: 10MB
- Таймаут запроса: 30 секунд
- Лимит запросов: 60 запросов в минуту
- Пагинация: максимум 50 элементов на страницу 