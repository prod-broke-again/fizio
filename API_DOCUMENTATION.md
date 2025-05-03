# API Документация

## Содержание
- [Аутентификация](#аутентификация)
  - [Регистрация](#регистрация)
  - [Вход](#вход)
  - [Аутентификация через Telegram](#аутентификация-через-telegram)
  - [Связывание с Telegram](#связывание-с-telegram)
  - [Выход](#выход)
- [Профиль пользователя](#профиль-пользователя)
  - [Получение профиля](#получение-профиля)
  - [Обновление профиля](#обновление-профиля)
  - [Управление фитнес-целями](#управление-фитнес-целями)
- [Прогресс пользователя](#прогресс-пользователя)
  - [Получение дневного прогресса](#получение-дневного-прогресса)
  - [Обновление прогресса](#обновление-прогресса)
- [Тренировки](#тренировки)
  - [Получение расписания тренировок](#получение-расписания-тренировок)
  - [Создание тренировки](#создание-тренировки)
  - [Обновление тренировки](#обновление-тренировки)
  - [Удаление тренировки](#удаление-тренировки)
- [Чат с AI-ассистентом](#чат-с-ai-ассистентом)
  - [Отправка сообщения](#отправка-сообщения)
  - [Получение истории чата](#получение-истории-чата)
  - [Отправка голосового сообщения](#отправка-голосового-сообщения)

## Аутентификация

### Регистрация
Регистрация нового пользователя в системе.

**URL**: `/api/auth/register`

**Метод**: `POST`

**Тело запроса**:
```json
{
  "name": "Иван Иванов",
  "email": "ivan@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "device_token": "optional-device-token"
}
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "gender": "not-specified",
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z"
    },
    "access_token": "токен_доступа",
    "token_type": "Bearer"
  },
  "message": "Регистрация успешно завершена"
}
```

### Вход
Вход в систему существующего пользователя.

**URL**: `/api/auth/login`

**Метод**: `POST`

**Тело запроса**:
```json
{
  "email": "ivan@example.com",
  "password": "password123",
  "device_token": "optional-device-token"
}
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "gender": "not-specified",
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z"
    },
    "access_token": "токен_доступа",
    "token_type": "Bearer"
  },
  "message": "Успешная авторизация"
}
```

### Аутентификация через Telegram
Аутентификация пользователя через Telegram.

**URL**: `/api/auth/telegram`

**Метод**: `POST`

**Тело запроса**:
```json
{
  "telegram_data": {
    "id": "telegram-user-id",
    "first_name": "Иван",
    "last_name": "Иванов",
    "username": "ivanov",
    "photo_url": "https://example.com/photo.jpg"
  }
}
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "telegram_id@example.com",
      "gender": "not-specified",
      "telegram_id": "telegram-user-id",
      "telegram_username": "ivanov",
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z"
    },
    "access_token": "токен_доступа",
    "token_type": "Bearer"
  },
  "message": "Успешная авторизация через Telegram"
}
```

### Связывание с Telegram
Связывание существующего аккаунта с Telegram.

**URL**: `/api/auth/telegram/link`

**Метод**: `POST`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Тело запроса**:
```json
{
  "telegram_data": {
    "id": "telegram-user-id",
    "first_name": "Иван",
    "last_name": "Иванов",
    "username": "ivanov",
    "photo_url": "https://example.com/photo.jpg"
  }
}
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "gender": "not-specified",
      "telegram_id": "telegram-user-id",
      "telegram_username": "ivanov",
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z"
    }
  },
  "message": "Аккаунт успешно связан с Telegram"
}
```

### Выход
Выход из системы (отзыв токена).

**URL**: `/api/auth/logout`

**Метод**: `POST`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Успешный ответ**:
```json
{
  "success": true,
  "message": "Успешный выход из системы"
}
```

## Профиль пользователя

### Получение профиля
Получение данных профиля текущего пользователя.

**URL**: `/api/user/profile`

**Метод**: `GET`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "gender": "not-specified",
      "fitness_goal": "weight-loss",
      "activity_level": "moderate",
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z",
      "avatar_url": "http://example.com/storage/avatars/user-1.jpg"
    }
  },
  "message": "Профиль пользователя"
}
```

### Обновление профиля
Обновление данных профиля пользователя, включая аватар.

**URL**: `/api/user/profile`

**Метод**: `POST`

**Заголовки**:
```
Authorization: Bearer токен_доступа
Content-Type: multipart/form-data
```

**Форма запроса**:
```
name: Иван Иванов
email: ivan@example.com
avatar: [файл изображения]
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "gender": "not-specified",
      "fitness_goal": "weight-loss",
      "activity_level": "moderate",
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z",
      "avatar": "avatars/user-1.jpg",
      "avatar_url": "http://example.com/storage/avatars/user-1.jpg"
    }
  },
  "message": "Профиль успешно обновлен"
}
```

### Управление фитнес-целями

#### Получение фитнес-цели

**URL**: `/api/user/fitness-goal`

**Метод**: `GET`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "goal": "weight-loss",
    "updated_at": "2023-08-15T12:00:00.000000Z"
  },
  "message": "Текущая цель фитнеса"
}
```

#### Сохранение фитнес-цели

**URL**: `/api/user/fitness-goal`

**Метод**: `POST`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Тело запроса**:
```json
{
  "goal": "weight-loss"
}
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "goal": "weight-loss",
    "updated_at": "2023-08-15T12:00:00.000000Z"
  },
  "message": "Цель фитнеса сохранена"
}
```

#### Получение пола пользователя

**URL**: `/api/user/gender`

**Метод**: `GET`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "gender": "not-specified"
  },
  "message": "Пол пользователя"
}
```

## Прогресс пользователя

### Получение дневного прогресса
Получение текущего дневного прогресса пользователя.

**URL**: `/api/progress/daily`

**Метод**: `GET`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "progress": {
      "id": 1,
      "user_id": 1,
      "calories": 500,
      "steps": 8000,
      "workout_time": 30,
      "water_intake": 1500,
      "daily_progress": 65,
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z"
    }
  },
  "message": "Дневной прогресс пользователя"
}
```

### Обновление прогресса
Обновление параметров прогресса пользователя.

**URL**: `/api/progress/update`

**Метод**: `POST`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Тело запроса**:
```json
{
  "calories": 500,
  "steps": 8000,
  "workout_time": 30,
  "water_intake": 1500
}
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "progress": {
      "id": 1,
      "user_id": 1,
      "calories": 500,
      "steps": 8000,
      "workout_time": 30,
      "water_intake": 1500,
      "daily_progress": 65,
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z"
    }
  },
  "message": "Прогресс успешно обновлен"
}
```

## Тренировки

### Получение расписания тренировок
Получение списка тренировок пользователя.

**URL**: `/api/workouts/schedule`

**Метод**: `GET`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "workouts": [
      {
        "id": 1,
        "user_id": 1,
        "title": "Кардио тренировка",
        "description": "Бег и прыжки",
        "date": "2023-08-16T15:00:00.000000Z",
        "duration": 45,
        "calories": 300,
        "image_url": "http://example.com/workouts/cardio.jpg",
        "created_at": "2023-08-15T12:00:00.000000Z",
        "updated_at": "2023-08-15T12:00:00.000000Z"
      }
    ]
  },
  "message": "Расписание тренировок"
}
```

### Создание тренировки
Создание новой тренировки.

**URL**: `/api/workouts`

**Метод**: `POST`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Тело запроса**:
```json
{
  "title": "Силовая тренировка",
  "description": "Приседания, отжимания, подтягивания",
  "date": "2023-08-17T16:00:00.000000Z",
  "duration": 60,
  "calories": 400,
  "image_url": "http://example.com/workouts/strength.jpg"
}
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "workout": {
      "id": 2,
      "user_id": 1,
      "title": "Силовая тренировка",
      "description": "Приседания, отжимания, подтягивания",
      "date": "2023-08-17T16:00:00.000000Z",
      "duration": 60,
      "calories": 400,
      "image_url": "http://example.com/workouts/strength.jpg",
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T12:00:00.000000Z"
    }
  },
  "message": "Тренировка успешно создана"
}
```

### Обновление тренировки
Обновление существующей тренировки.

**URL**: `/api/workouts/{id}`

**Метод**: `PUT`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Тело запроса**:
```json
{
  "title": "Силовая тренировка (обновлено)",
  "description": "Приседания, отжимания, подтягивания, планка",
  "date": "2023-08-17T17:00:00.000000Z",
  "duration": 75,
  "calories": 450,
  "image_url": "http://example.com/workouts/strength-updated.jpg"
}
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": {
    "workout": {
      "id": 2,
      "user_id": 1,
      "title": "Силовая тренировка (обновлено)",
      "description": "Приседания, отжимания, подтягивания, планка",
      "date": "2023-08-17T17:00:00.000000Z",
      "duration": 75,
      "calories": 450,
      "image_url": "http://example.com/workouts/strength-updated.jpg",
      "created_at": "2023-08-15T12:00:00.000000Z",
      "updated_at": "2023-08-15T13:00:00.000000Z"
    }
  },
  "message": "Тренировка успешно обновлена"
}
```

### Удаление тренировки
Удаление существующей тренировки.

**URL**: `/api/workouts/{id}`

**Метод**: `DELETE`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Успешный ответ**:
```json
{
  "success": true,
  "message": "Тренировка успешно удалена"
}
```

## Чат с AI-ассистентом

### Отправка сообщения
Отправка сообщения AI-ассистенту и получение ответа.

**URL**: `/api/chat/send`

**Метод**: `POST`

**Заголовки**:
```
Authorization: Bearer токен_доступа
Content-Type: application/json
```

**Тело запроса**:
```json
{
  "message": "Как часто нужно делать кардио?"
}
```

**Успешный ответ (синхронный режим)**:
```json
{
  "success": true,
  "data": {
    "message": "Как часто нужно делать кардио?",
    "response": "Частота кардиотренировок зависит от ваших целей, уровня подготовки и общего состояния здоровья. Вот общие рекомендации:\n\n1. Для общего здоровья (поддержка сердца, тонус, профилактика болезней) 3–5 раз в неделю по 20–60 минут.\n\nИнтенсивность: умеренная (например, быстрая ходьба, легкий бег, плавание).",
    "created_at": "2023-08-15T12:00:00.000000Z"
  }
}
```

**Успешный ответ (асинхронный режим с очередью)**:
```json
{
  "success": true,
  "message": "Ваше сообщение обрабатывается",
  "data": {
    "message_id": 1,
    "message": "Как часто нужно делать кардио?",
    "is_processing": true
  }
}
```

### Получение истории чата
Получение истории переписки с AI-ассистентом.

**URL**: `/api/chat/history`

**Метод**: `GET`

**Заголовки**:
```
Authorization: Bearer токен_доступа
```

**Успешный ответ**:
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "message": "Какие продукты богаты белком?",
      "response": "Рекомендации по потреблению белка:\n\n1. Для обычного человека: 0.8-1 г на кг веса тела\n2. Для активных людей: 1.2-1.7 г на кг веса\n3. Для наращивания мышц: 1.6-2.2 г на кг веса\n\nХорошие источники белка: курица, рыба, яйца, молочные продукты, бобовые, тофу, орехи и семена. Для оптимального усвоения распределяйте белок равномерно в течение дня.",
      "created_at": "2023-08-16T10:15:00.000000Z"
    },
    {
      "id": 1,
      "message": "Как часто нужно делать кардио?",
      "response": "Частота кардиотренировок зависит от ваших целей, уровня подготовки и общего состояния здоровья. Вот общие рекомендации:\n\n1. Для общего здоровья (поддержка сердца, тонус, профилактика болезней) 3–5 раз в неделю по 20–60 минут.\n\nИнтенсивность: умеренная (например, быстрая ходьба, легкий бег, плавание).",
      "created_at": "2023-08-15T12:00:00.000000Z"
    }
  ]
}
```

### Отправка голосового сообщения
Отправка голосового сообщения для AI-ассистента.

**URL**: `/api/chat/voice`

**Метод**: `POST`

**Заголовки**:
```
Authorization: Bearer токен_доступа
Content-Type: multipart/form-data
```

**Форма запроса**:
```
audio: [аудио файл]
```

**Успешный ответ**:
```json
{
  "success": true,
  "message": "Голосовое сообщение успешно обработано",
  "data": {
    "audio_path": "voice_messages/file.mp3"
  }
}
``` 