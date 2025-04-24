# Документация API для фитнес-приложения

## Обзор

Данное API предназначено для обслуживания мобильного фитнес-приложения на Ionic Vue. API предоставляет эндпоинты для регистрации, авторизации пользователей и управления их фитнес-целями.

### Базовый URL

```
https://fizio.online/api
```

### Формат ответов

Все ответы возвращаются в формате JSON со следующей стандартной структурой:

```json
{
  "success": true/false,
  "data": { ... },
  "message": "Описание результата операции",
  "errors": { ... } // только при ошибках
}
```

### Аутентификация

API использует токены Bearer для аутентификации. После регистрации или входа в систему, клиент получает токен доступа, который необходимо передавать в заголовке `Authorization` для всех защищенных эндпоинтов:

```
Authorization: Bearer {access_token}
```

## Эндпоинты API

### Аутентификация

#### Регистрация пользователя

Создает новую учетную запись пользователя и возвращает токен доступа.

**URL**: `/auth/register`

**Метод**: `POST`

**Аутентификация**: Не требуется

**Параметры запроса**:

| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|----------|
| name | string | Да | Имя пользователя |
| email | string | Да | Email пользователя (должен быть уникальным) |
| password | string | Да | Пароль (минимум 8 символов) |
| password_confirmation | string | Да | Подтверждение пароля |
| gender | string | Нет | Пол пользователя (male/female/non-binary/not-specified) |
| device_token | string | Нет | FCM токен для push-уведомлений |

**Пример запроса**:
```json
{
  "name": "Иван Иванов",
  "email": "ivan@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "gender": "male",
  "device_token": "fcm-token-example"
}
```

**Успешный ответ (200 OK)**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "gender": "male",
      "created_at": "2025-04-24T18:27:38.000000Z",
      "updated_at": "2025-04-24T18:27:38.000000Z"
    },
    "access_token": "1|RmjAQ0Pk6ZlwgISasUiF8ZCwwPmSnpc0LV6wW1fPdffa8c3b",
    "token_type": "Bearer"
  },
  "message": "Регистрация успешно завершена"
}
```

**Ответ с ошибкой (422 Unprocessable Entity)**:
```json
{
  "success": false,
  "message": "Ошибка валидации",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

#### Авторизация пользователя

Аутентифицирует пользователя и возвращает токен доступа.

**URL**: `/auth/login`

**Метод**: `POST`

**Аутентификация**: Не требуется

**Параметры запроса**:

| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|----------|
| email | string | Да | Email пользователя |
| password | string | Да | Пароль пользователя |
| device_token | string | Нет | FCM токен для push-уведомлений |

**Пример запроса**:
```json
{
  "email": "ivan@example.com",
  "password": "password123",
  "device_token": "fcm-token-example"
}
```

**Успешный ответ (200 OK)**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "gender": "male",
      "fitness_goal": "weight-loss",
      "activity_level": null,
      "created_at": "2025-04-24T18:27:38.000000Z",
      "updated_at": "2025-04-24T18:27:38.000000Z"
    },
    "access_token": "2|xbL571nsRHbRlilPqrMUgAfO7QoOfGInregwwdRt9331ab38",
    "token_type": "Bearer"
  },
  "message": "Авторизация успешна"
}
```

**Ответ с ошибкой (401 Unauthorized)**:
```json
{
  "success": false,
  "message": "Неверный email или пароль"
}
```

#### Выход из системы

Инвалидирует текущий токен доступа пользователя.

**URL**: `/auth/logout`

**Метод**: `POST`

**Аутентификация**: Требуется

**Параметры запроса**: Отсутствуют

**Пример запроса**:
```
Authorization: Bearer 1|RmjAQ0Pk6ZlwgISasUiF8ZCwwPmSnpc0LV6wW1fPdffa8c3b
```

**Успешный ответ (200 OK)**:
```json
{
  "success": true,
  "message": "Выход выполнен успешно"
}
```

### Управление профилем пользователя

#### Получение профиля пользователя

Возвращает информацию о текущем пользователе.

**URL**: `/user/profile`

**Метод**: `GET`

**Аутентификация**: Требуется

**Параметры запроса**: Отсутствуют

**Пример запроса**:
```
Authorization: Bearer 1|RmjAQ0Pk6ZlwgISasUiF8ZCwwPmSnpc0LV6wW1fPdffa8c3b
```

**Успешный ответ (200 OK)**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Иван Иванов",
      "email": "ivan@example.com",
      "email_verified_at": null,
      "gender": "male",
      "fitness_goal": "weight-loss",
      "activity_level": null,
      "device_token": "fcm-token-example",
      "created_at": "2025-04-24T18:27:38.000000Z",
      "updated_at": "2025-04-24T18:27:38.000000Z"
    }
  },
  "message": "Профиль пользователя"
}
```

### Управление целями фитнеса

#### Сохранение цели фитнеса

Сохраняет или обновляет цель фитнеса для текущего пользователя.

**URL**: `/user/fitness-goal`

**Метод**: `POST`

**Аутентификация**: Требуется

**Параметры запроса**:

| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|----------|
| goal | string | Да | Цель фитнеса (weight-loss/muscle-gain/maintenance) |

**Пример запроса**:
```json
{
  "goal": "weight-loss"
}
```

**Успешный ответ (200 OK)**:
```json
{
  "success": true,
  "data": {
    "goal": "weight-loss",
    "updated_at": "2025-04-24T18:27:38.000000Z"
  },
  "message": "Цель фитнеса сохранена"
}
```

**Ответ с ошибкой (422 Unprocessable Entity)**:
```json
{
  "success": false,
  "message": "Ошибка валидации",
  "errors": {
    "goal": [
      "The selected goal is invalid."
    ]
  }
}
```

#### Получение текущей цели фитнеса

Возвращает текущую цель фитнеса пользователя.

**URL**: `/user/fitness-goal`

**Метод**: `GET`

**Аутентификация**: Требуется

**Параметры запроса**: Отсутствуют

**Пример запроса**:
```
Authorization: Bearer 1|RmjAQ0Pk6ZlwgISasUiF8ZCwwPmSnpc0LV6wW1fPdffa8c3b
```

**Успешный ответ (200 OK)**:
```json
{
  "success": true,
  "data": {
    "goal": "weight-loss",
    "updated_at": "2025-04-24T18:27:38.000000Z"
  },
  "message": "Текущая цель фитнеса"
}
```

## Коды ошибок и их обработка

API использует стандартные HTTP коды состояния для индикации успеха или неудачи запроса:

| Код | Описание |
|-----|----------|
| 200 | OK - Запрос успешно обработан |
| 401 | Unauthorized - Требуется аутентификация или аутентификация не удалась |
| 403 | Forbidden - Доступ запрещен |
| 404 | Not Found - Ресурс не найден |
| 422 | Unprocessable Entity - Ошибка валидации данных |
| 500 | Internal Server Error - Внутренняя ошибка сервера |

При ошибке валидации возвращается объект `errors` с информацией о проблемных полях и описанием ошибок.

## Инструменты для тестирования API

Для тестирования API рекомендуется использовать:

1. [Postman](https://www.postman.com/) - удобный инструмент с графическим интерфейсом
2. [curl](https://curl.haxx.se/) - инструмент командной строки
3. Bash-скрипт `test_api.sh` - автоматизированное тестирование всех эндпоинтов

Более подробную информацию о тестировании API можно найти в файле [API_TESTING.md](API_TESTING.md). 