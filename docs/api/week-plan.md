# Week Plan API - Документация

## Обзор

Week Plan API теперь интегрирован с новой системой питания `meals/meal_items`. API возвращает данные в том же формате, что и раньше, но теперь читает информацию из новой системы, что обеспечивает единообразие данных.

## Базовый URL

```
https://fizio.online/api/week-plan
```

## Аутентификация

Все запросы требуют Bearer токен в заголовке:

```
Authorization: Bearer {your_token}
```

## Заголовки

- `Authorization: Bearer {token}` - обязательный
- `x-timezone: Europe/Moscow` - опциональный, по умолчанию используется `app.timezone`

## Endpoints

### 1. Получение недельного плана

**GET** `/week-plan`

Возвращает план на текущую неделю (понедельник - воскресенье) с группировкой по дням.

#### Параметры запроса

- `x-timezone` - часовой пояс для расчета начала недели

#### Пример запроса

```bash
curl -H "Authorization: Bearer {token}" \
     -H "x-timezone: Europe/Moscow" \
     "https://fizio.online/api/week-plan"
```

#### Пример ответа

```json
[
  {
    "date": "2025-08-11",
    "dayName": "понедельник",
    "meals": []
  },
  {
    "date": "2025-08-12", 
    "dayName": "вторник",
    "meals": []
  },
  {
    "date": "2025-08-13",
    "dayName": "среда", 
    "meals": []
  },
  {
    "date": "2025-08-14",
    "dayName": "четверг",
    "meals": [
      {
        "id": "0198a64a-a450-7031-8b96-71ba563c6e1b",
        "name": "Завтрак",
        "mealType": "breakfast",
        "time": "08:00",
        "completed": true,
        "calories": 1080.0,
        "proteins": 23.5,
        "fats": 33.1,
        "carbs": 169.7,
        "items": [
          {
            "id": 4,
            "calories": "336.00",
            "proteins": "12.80",
            "fats": "3.20",
            "carbs": "64.00",
            "product": {
              "id": "4600935030675",
              "name": "Гречка",
              "image": "https://images.openfoodfacts.org/images/products/460/093/503/0675/front_ru.4.400.jpg"
            },
            "grams": "100.00"
          },
          {
            "id": 3,
            "calories": "260.00",
            "proteins": "7.00",
            "fats": "4.50",
            "carbs": "47.00",
            "product": {
              "id": "4605829006040",
              "name": "Harrys хлеб для тостов",
              "image": "https://images.openfoodfacts.org/images/products/460/582/900/6040/front_ru.7.400.jpg"
            },
            "grams": "100.00"
          }
        ]
      }
    ]
  },
  {
    "date": "2025-08-15",
    "dayName": "пятница",
    "meals": []
  },
  {
    "date": "2025-08-16",
    "dayName": "суббота",
    "meals": []
  },
  {
    "date": "2025-08-17",
    "dayName": "воскресенье",
    "meals": []
  }
]
```

### 2. Получение плана на конкретный день

**GET** `/week-plan/{date}`

Возвращает план на указанную дату.

#### Параметры пути

- `date` - дата в формате `YYYY-MM-DD`

#### Пример запроса

```bash
curl -H "Authorization: Bearer {token}" \
     "https://fizio.online/api/week-plan/2025-08-14"
```

#### Пример ответа

```json
{
  "date": "2025-08-14",
  "dayName": "четверг",
  "meals": [
    {
      "id": "0198a64a-a450-7031-8b96-71ba563c6e1b",
      "name": "Завтрак",
      "mealType": "breakfast",
      "time": "08:00",
      "completed": true,
      "calories": 1080.0,
      "proteins": 23.5,
      "fats": 33.1,
      "carbs": 169.7,
      "items": [
        {
          "id": 4,
          "calories": "336.00",
          "proteins": "12.80",
          "fats": "3.20",
          "carbs": "64.00",
          "product": {
            "id": "4600935030675",
            "name": "Гречка",
            "image": "https://images.openfoodfacts.org/images/products/460/093/503/0675/front_ru.4.400.jpg"
          },
          "grams": "100.00"
        }
      ]
    }
  ]
}
```

### 3. Создание нового приёма пищи

**POST** `/week-plan/{date}/meal`

Создает новый приём пищи на указанную дату.

#### Параметры пути

- `date` - дата в формате `YYYY-MM-DD`

#### Тело запроса

```json
{
  "name": "Завтрак",
  "type": "breakfast",
  "time": "08:00",
  "items": [
    {
      "product_id": "4600935030675",
      "grams": 100,
      "calories": 336,
      "proteins": 12.8,
      "fats": 3.2,
      "carbs": 64
    },
    {
      "free_text": "Яблоко",
      "grams": 150,
      "calories": 78,
      "proteins": 0.4,
      "fats": 0.2,
      "carbs": 20.4
    }
  ]
}
```

#### Поля запроса

| Поле | Тип | Обязательное | Описание |
|------|-----|---------------|----------|
| `name` | string | Да | Название приёма пищи |
| `type` | string | Да | Тип приёма пищи: `breakfast`, `lunch`, `dinner`, `snack` |
| `time` | string | Нет | Время приёма пищи в формате `HH:MM` |
| `items` | array | Нет | Массив элементов питания |

#### Поля элементов питания

| Поле | Тип | Обязательное | Описание |
|------|-----|---------------|----------|
| `product_id` | string | Нет* | Штрих-код продукта из базы |
| `free_text` | string | Нет* | Свободный текст продукта |
| `grams` | number | Нет | Вес в граммах |
| `servings` | number | Нет | Количество порций |
| `calories` | number | Нет | Калории |
| `proteins` | number | Нет | Белки (г) |
| `fats` | number | Нет | Жиры (г) |
| `carbs` | number | Нет | Углеводы (г) |

*Должен быть указан либо `product_id`, либо `free_text`

#### Пример ответа

```json
[
  {
    "id": "0198a64a-a450-7031-8b96-71ba563c6e1b",
    "name": "Завтрак",
    "mealType": "breakfast",
    "time": "08:00",
    "completed": false,
    "calories": 414.0,
    "proteins": 13.2,
    "fats": 3.4,
    "carbs": 84.4,
    "items": [
      {
        "id": 5,
        "calories": "336.00",
        "proteins": "12.80",
        "fats": "3.20",
        "carbs": "64.00",
        "product": {
          "id": "4600935030675",
          "name": "Гречка",
          "image": "https://images.openfoodfacts.org/images/products/460/093/503/0675/front_ru.4.400.jpg"
        },
        "grams": "100.00"
      },
      {
        "id": 6,
        "calories": "78.00",
        "proteins": "0.40",
        "fats": "0.20",
        "carbs": "20.40",
        "free_text": "Яблоко",
        "grams": "150.00"
      }
    ]
  }
]
```

### 4. Переключение статуса приёма пищи

**PATCH** `/week-plan/{date}/meal/{id}/toggle-complete`

Переключает статус "выполнено/не выполнено" для указанного приёма пищи.

#### Параметры пути

- `date` - дата в формате `YYYY-MM-DD`
- `id` - UUID приёма пищи

#### Пример запроса

```bash
curl -X PATCH \
     -H "Authorization: Bearer {token}" \
     "https://fizio.online/api/week-plan/2025-08-14/meal/0198a64a-a450-7031-8b96-71ba563c6e1b/toggle-complete"
```

#### Пример ответа

```json
{
  "success": true,
  "meal": {
    "id": "0198a64a-a450-7031-8b96-71ba563c6e1b",
    "name": "Завтрак",
    "mealType": "breakfast",
    "time": "08:00",
    "completed": true,
    "calories": 1080.0,
    "proteins": 23.5,
    "fats": 33.1,
    "carbs": 169.7,
    "items": [...]
  }
}
```

### 5. Обновление прогресса дня

**PATCH** `/week-plan/{date}/progress`

Обновляет общий прогресс дня (для тренировок).

#### Параметры пути

- `date` - дата в формате `YYYY-MM-DD`

#### Тело запроса

```json
{
  "progress": 75
}
```

#### Пример ответа

```json
{
  "id": 123,
  "user_id": 1,
  "date": "2025-08-14",
  "meals": [],
  "workouts": [...],
  "progress": 75,
  "created_at": "2025-08-14T00:00:00.000000Z",
  "updated_at": "2025-08-14T12:00:00.000000Z"
}
```

### 6. Добавление тренировки

**POST** `/week-plan/{date}/workout`

Добавляет тренировку в план дня (использует старую систему WeekPlan).

#### Параметры пути

- `date` - дата в формате `YYYY-MM-DD`

#### Тело запроса

```json
{
  "workout_id": 1,
  "time": "18:00"
}
```

## Структура данных

### День недели

```json
{
  "date": "2025-08-14",
  "dayName": "четверг",
  "meals": [...]
}
```

| Поле | Тип | Описание |
|------|-----|----------|
| `date` | string | Дата в формате `YYYY-MM-DD` |
| `dayName` | string | Название дня недели на русском языке |
| `meals` | array | Массив приёмов пищи |

### Приём пищи

```json
{
  "id": "0198a64a-a450-7031-8b96-71ba563c6e1b",
  "name": "Завтрак",
  "mealType": "breakfast",
  "time": "08:00",
  "completed": true,
  "calories": 1080.0,
  "proteins": 23.5,
  "fats": 33.1,
  "carbs": 169.7,
  "items": [...]
}
```

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | string | UUID приёма пищи |
| `name` | string | Название приёма пищи |
| `mealType` | string | Тип: `breakfast`, `lunch`, `dinner`, `snack` |
| `time` | string | Время приёма пищи в формате `HH:MM` |
| `completed` | boolean | Статус выполнения |
| `calories` | number | Общие калории |
| `proteins` | number | Общие белки (г) |
| `fats` | number | Общие жиры (г) |
| `carbs` | number | Общие углеводы (г) |
| `items` | array | Массив элементов питания |

### Элемент питания

```json
{
  "id": 4,
  "calories": "336.00",
  "proteins": "12.80",
  "fats": "3.20",
  "carbs": "64.00",
  "product": {
    "id": "4600935030675",
    "name": "Гречка",
    "image": "https://images.openfoodfacts.org/images/products/460/093/503/0675/front_ru.4.400.jpg"
  },
  "grams": "100.00"
}
```

#### Для продуктов из базы:

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | integer | ID элемента питания |
| `calories` | string | Калории |
| `proteins` | string | Белки (г) |
| `fats` | string | Жиры (г) |
| `carbs` | string | Углеводы (г) |
| `product` | object | Информация о продукте |
| `product.id` | string | Штрих-код продукта |
| `product.name` | string | Название продукта |
| `product.image` | string | URL изображения |
| `grams` | string | Вес в граммах |
| `servings` | string | Количество порций (если указано) |

#### Для свободного текста:

```json
{
  "id": 6,
  "calories": "78.00",
  "proteins": "0.40",
  "fats": "0.20",
  "carbs": "20.40",
  "free_text": "Яблоко",
  "grams": "150.00"
}
```

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | integer | ID элемента питания |
| `calories` | string | Калории |
| `proteins` | string | Белки (г) |
| `fats` | string | Жиры (г) |
| `carbs` | string | Углеводы (г) |
| `free_text` | string | Свободный текст продукта |
| `grams` | string | Вес в граммах |
| `servings` | string | Количество порций (если указано) |

## Типы приёмов пищи

- `breakfast` - Завтрак
- `lunch` - Обед  
- `dinner` - Ужин
- `snack` - Перекус

## Названия дней недели

- `понедельник` - Monday
- `вторник` - Tuesday
- `среда` - Wednesday
- `четверг` - Thursday
- `пятница` - Friday
- `суббота` - Saturday
- `воскресенье` - Sunday

## Коды ошибок

| Код | Описание |
|-----|----------|
| `401` | Не авторизован |
| `403` | Доступ запрещён |
| `404` | День не найден |
| `422` | Ошибка валидации |
| `500` | Внутренняя ошибка сервера |

## Примеры использования

### Получение недели с группировкой по дням

```javascript
const response = await fetch('/api/week-plan', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'x-timezone': 'Europe/Moscow'
  }
});

const weekData = await response.json();

// Группировка по дням
weekData.forEach(day => {
  console.log(`${day.date} (${day.dayName}): ${day.meals.length} приёмов пищи`);
  
  day.meals.forEach(meal => {
    console.log(`- ${meal.name} (${meal.mealType}): ${meal.calories} ккал`);
  });
});
```

### Переключение статуса приёма пищи

```javascript
const toggleMeal = async (date, mealId) => {
  const response = await fetch(`/api/week-plan/${date}/meal/${mealId}/toggle-complete`, {
    method: 'PATCH',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const result = await response.json();
  
  if (result.success) {
    console.log(`Приём пищи ${result.meal.completed ? 'выполнен' : 'не выполнен'}`);
  }
};
```

### Создание нового приёма пищи

```javascript
const createMeal = async (date, mealData) => {
  const response = await fetch(`/api/week-plan/${date}/meal`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(mealData)
  });
  
  const result = await response.json();
  console.log('Создан приём пищи:', result[0]);
};
```

## Интеграция с новой системой

Week Plan API теперь полностью интегрирован с новой системой питания:

- **Данные читаются** из таблиц `meals` и `meal_items`
- **Новые приёмы пищи** автоматически появляются в недельном плане
- **Изменения статуса** синхронизируются между системами
- **Формат ответа** остается совместимым с существующим фронтендом

## Примечания

1. **Часовой пояс**: API учитывает часовой пояс пользователя для расчета начала недели
2. **Автоматический пересчёт**: Общие значения (калории, белки, жиры, углеводы) автоматически пересчитываются при изменении элементов
3. **Обратная совместимость**: API возвращает данные в том же формате, что и раньше
4. **UUID**: Приёмы пищи используют UUID вместо auto-increment ID
5. **Продукты**: Ссылки на продукты используют штрих-код (`code`) для внешнего API, но внутренне связываются по `id`
