# API Документация

## Общая информация

- Базовый URL: `https://fizio.online`
- Все запросы должны содержать заголовок `Accept: application/json`
- Для защищенных эндпоинтов требуется заголовок `Authorization: Bearer {token}`
- Лимит запросов: 60 запросов в минуту
- Пагинация: максимум 50 элементов на страницу

## Аутентификация

### Регистрация
```http
POST /auth/register
```

Параметры:
- `name` (string, required) - Имя пользователя
- `email` (string, required) - Email пользователя
- `password` (string, required) - Пароль (минимум 8 символов)
- `password_confirmation` (string, required) - Подтверждение пароля

### Вход
```http
POST /auth/login
```

Параметры:
- `email` (string, required) - Email пользователя
- `password` (string, required) - Пароль

### Выход
```http
POST /auth/logout
```

Требуется токен авторизации.

### Обновление токена
```http
POST /auth/refresh
```

Требуется токен авторизации.

## Пользователь

### Получение профиля
```http
GET /user/profile
```

Требуется токен авторизации.

### Обновление профиля
```http
POST /user/profile
```

Требуется токен авторизации.

Параметры:
- `name` (string, optional) - Новое имя
- `email` (string, optional) - Новый email
- `current_password` (string, required if changing password) - Текущий пароль
- `password` (string, optional) - Новый пароль
- `password_confirmation` (string, required if changing password) - Подтверждение нового пароля

### Сохранение целей фитнеса
```http
POST /user/fitness-goal
```

Требуется токен авторизации.

Параметры:
- `goal_type` (string, required) - Тип цели (weight_loss, muscle_gain, maintenance)
- `target_weight` (float, optional) - Целевой вес
- `target_calories` (integer, optional) - Целевые калории
- `target_steps` (integer, optional) - Целевые шаги
- `target_workout_time` (integer, optional) - Целевое время тренировок
- `target_water_intake` (float, optional) - Целевое потребление воды

### Получение целей фитнеса
```http
GET /user/fitness-goal
```

Требуется токен авторизации.

### Получение пола пользователя
```http
GET /user/gender
```

Требуется токен авторизации.

## Прогресс и статистика

### Получение дневного прогресса
```http
GET /progress/daily
```

Требуется токен авторизации.

### Обновление прогресса
```http
POST /progress/update
```

Требуется токен авторизации.

Параметры:
- `calories` (integer, required) - Потребленные калории
- `steps` (integer, required) - Количество шагов
- `workout_time` (integer, required) - Время тренировки в минутах
- `water_intake` (float, required) - Потребление воды в литрах

## Тренировки

### Получение списка тренировок
```http
GET /workouts
```

Требуется токен авторизации.

Параметры:
- `category` (string, optional) - Фильтр по категории
- `difficulty` (string, optional) - Фильтр по сложности
- `page` (integer, optional) - Номер страницы
- `per_page` (integer, optional) - Количество элементов на странице

### Получение рекомендуемых тренировок
```http
GET /workouts/recommended
```

Требуется токен авторизации.

### Добавление/удаление тренировки из избранного
```http
POST /workouts/{workout}/favorite
DELETE /workouts/{workout}/favorite
```

Требуется токен авторизации.

## Уведомления

### Получение списка уведомлений
```http
GET /notifications
```

Требуется токен авторизации.

### Отметка уведомления как прочитанного
```http
POST /notifications/{id}/read
```

Требуется токен авторизации.

### Отметка всех уведомлений как прочитанных
```http
POST /notifications/read-all
```

Требуется токен авторизации.

## Питание

### Получение дневного питания
```http
GET /nutrition/daily
```

Требуется токен авторизации.

### Добавление приема пищи
```http
POST /nutrition/meals
```

Требуется токен авторизации.

Параметры:
- `type` (string, required) - Тип приема пищи (breakfast, lunch, dinner, snack)
- `consumed_at` (datetime, required) - Время приема пищи
- `foods` (array, required) - Список продуктов
  - `name` (string, required) - Название продукта
  - `calories` (float, required) - Калории
  - `protein` (float, required) - Белки
  - `carbs` (float, required) - Углеводы
  - `fat` (float, required) - Жиры
  - `portion_size` (float, required) - Размер порции
  - `portion_unit` (string, required) - Единица измерения порции

## Интеграции

### Apple Watch

#### Подключение Apple Watch
```http
POST /integrations/apple-watch/connect
```

Требуется токен авторизации.

#### Синхронизация данных с Apple Watch
```http
POST /integrations/apple-watch/sync
```

Требуется токен авторизации.

### HealthKit

#### Синхронизация данных с HealthKit
```http
POST /healthkit/sync
```

Требуется токен авторизации.

## Чат с AI-ассистентом

### Отправка сообщения
```http
POST /chat/send
```

Требуется токен авторизации.

Параметры:
- `message` (string, required) - Текст сообщения

### Получение истории чата
```http
GET /chat/history
```

Требуется токен авторизации.

### Обработка голосового сообщения
```http
POST /chat/voice
```

Требуется токен авторизации.

Параметры:
- `audio` (file, required) - Аудиофайл в формате MP3 или WAV

### Очистка истории чата
```http
DELETE /chat/clear
```

Требуется токен авторизации.

## Поиск продуктов питания

### FatSecret

#### Получение токена
```http
POST /fatsecret/token
```

#### Поиск продуктов
```http
GET /fatsecret/foods/search
```

Параметры:
- `query` (string, required) - Поисковый запрос
- `page` (integer, optional) - Номер страницы
- `per_page` (integer, optional) - Количество элементов на странице

#### Получение информации о продукте
```http
GET /fatsecret/foods/{foodId}
```

### OpenFoodFacts

#### Поиск продуктов
```http
GET /openfoodfacts/products/search
```

Параметры:
- `query` (string, required) - Поисковый запрос
- `page` (integer, optional) - Номер страницы
- `per_page` (integer, optional) - Количество элементов на странице

#### Получение продукта по штрих-коду
```http
GET /openfoodfacts/products/barcode/{barcode}
```

### Spoonacular

#### Поиск продуктов
```http
GET /spoonacular/products/search
```

Параметры:
- `query` (string, required) - Поисковый запрос
- `page` (integer, optional) - Номер страницы
- `per_page` (integer, optional) - Количество элементов на странице

#### Получение продукта по UPC
```http
GET /spoonacular/products/upc/{upc}
``` 