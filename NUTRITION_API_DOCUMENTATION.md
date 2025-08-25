# 🍽️ Fizio Nutrition API Documentation

## Обзор системы

Система питания Fizio позволяет пользователям создавать приёмы пищи с несколькими продуктами, автоматически рассчитывая общие БЖУ (белки, жиры, углеводы) и калории.

## 🗄️ Структура базы данных

### Таблица `meal_items`
```sql
CREATE TABLE meal_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meal_id VARCHAR(255) NOT NULL,           -- UUID приёма пищи
    product_id BIGINT UNSIGNED NULL,         -- ID продукта из базы (опционально)
    free_text VARCHAR(255) NULL,             -- Свободный текст продукта
    grams DECIMAL(8,2) NULL,                -- Вес в граммах
    servings DECIMAL(6,2) NULL,             -- Количество порций
    calories DECIMAL(8,2) DEFAULT 0,        -- Калории на позицию
    proteins DECIMAL(8,2) DEFAULT 0,        -- Белки на позицию (г)
    fats DECIMAL(8,2) DEFAULT 0,            -- Жиры на позицию (г)
    carbs DECIMAL(8,2) DEFAULT 0,           -- Углеводы на позицию (г)
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Индексы
    INDEX meal_items_meal_id_product_id_index (meal_id, product_id),
    INDEX meal_items_free_text_index (free_text),
    
    -- Foreign keys
    FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### Связи
- `meal_items.meal_id` → `meals.id` (cascade delete)
- `meal_items.product_id` → `products.id` (cascade delete)

## 🚀 API Endpoints

### Базовый URL
```
https://fizio.online/api
```

### Аутентификация
Все запросы требуют Bearer токен в заголовке:
```
Authorization: Bearer {token}
```

## 📋 Управление приёмами пищи

### 1. Создать приём пищи
```http
POST /meals
```

**Тело запроса:**
```json
{
    "name": "Завтрак",
    "type": "breakfast",
    "date": "2025-08-13",
    "time": "08:00",
    "completed": false
}
```

**Ответ:**
```json
{
    "message": "Приём пищи успешно создан",
    "meal": {
        "id": "uuid-here",
        "name": "Завтрак",
        "type": "breakfast",
        "date": "2025-08-13",
        "time": "08:00",
        "completed": false,
        "user_id": 1,
        "created_at": "2025-08-13T08:00:00.000000Z",
        "updated_at": "2025-08-13T08:00:00.000000Z"
    },
    "totals": {
        "calories": 0,
        "proteins": 0,
        "fats": 0,
        "carbs": 0,
        "items_count": 0
    }
}
```

### 2. Получить приём пищи с элементами
```http
GET /meals/{meal_id}
```

**Ответ:**
```json
{
    "meal": {
        "id": "uuid-here",
        "name": "Завтрак",
        "type": "breakfast",
        "date": "2025-08-13",
        "time": "08:00",
        "completed": false,
        "user_id": 1,
        "created_at": "2025-08-13T08:00:00.000000Z",
        "updated_at": "2025-08-13T08:00:00.000000Z",
        "items": [
            {
                "id": 1,
                "meal_id": "uuid-here",
                "product_id": 123,
                "free_text": null,
                "grams": 150.00,
                "servings": null,
                "calories": 225.00,
                "proteins": 12.00,
                "fats": 8.00,
                "carbs": 30.00,
                "created_at": "2025-08-13T08:00:00.000000Z",
                "updated_at": "2025-08-13T08:00:00.000000Z",
                "product_name": "Овсянка",
                "weight": 150.00,
                "portions": null,
                "is_free_text": false,
                "is_from_database": true,
                "product": {
                    "id": 123,
                    "name": "Овсянка",
                    "calories": 150,
                    "proteins": 8,
                    "fats": 5,
                    "carbs": 20
                }
            }
        ]
    },
    "totals": {
        "calories": 225.00,
        "proteins": 12.00,
        "fats": 8.00,
        "carbs": 30.00,
        "items_count": 1
    }
}
```

### 3. Обновить приём пищи
```http
PUT /meals/{meal_id}
```

**Тело запроса:**
```json
{
    "name": "Плотный завтрак",
    "completed": true
}
```

### 4. Удалить приём пищи
```http
DELETE /meals/{meal_id}
```

## 🥗 Управление элементами приёма пищи

### 1. Добавить продукт в приём
```http
POST /meals/{meal_id}/items
```

**Тело запроса (продукт из базы):**
```json
{
    "product_id": 123,
    "grams": 150,
    "calories": 225,
    "proteins": 12,
    "fats": 8,
    "carbs": 30
}
```

**Тело запроса (свободный текст):**
```json
{
    "free_text": "Домашний смузи",
    "calories": 180,
    "proteins": 8,
    "fats": 2,
    "carbs": 35
}
```

**Ответ:**
```json
{
    "message": "Продукт успешно добавлен в приём пищи",
    "meal_item": {
        "id": 1,
        "meal_id": "uuid-here",
        "product_id": 123,
        "free_text": null,
        "grams": 150.00,
        "servings": null,
        "calories": 225.00,
        "proteins": 12.00,
        "fats": 8.00,
        "carbs": 30.00,
        "created_at": "2025-08-13T08:00:00.000000Z",
        "updated_at": "2025-08-13T08:00:00.000000Z",
        "product_name": "Овсянка",
        "weight": 150.00,
        "portions": null,
        "is_free_text": false,
        "is_from_database": true
    },
    "meal_totals": {
        "calories": 225.00,
        "proteins": 12.00,
        "fats": 8.00,
        "carbs": 30.00
    }
}
```

### 2. Обновить элемент приёма
```http
PATCH /meals/{meal_id}/items/{item_id}
```

**Тело запроса:**
```json
{
    "grams": 200
}
```

**Примечание:** При изменении `grams` или `servings` система автоматически пересчитывает БЖУ/калории для продуктов из базы данных.

### 3. Удалить элемент приёма
```http
DELETE /meals/{meal_id}/items/{item_id}
```

## 🔄 Автоматический пересчёт

### Для продуктов из базы данных:
- **Граммы:** `ratio = grams / 100` (100г = базовая порция)
- **Порции:** `ratio = servings` (1 порция = базовая)

### Формулы:
```
calories = product.calories * ratio
proteins = product.proteins * ratio
fats = product.fats * ratio
carbs = product.carbs * ratio
```

## 📊 Модели данных

### MealItem
```php
class MealItem extends Model
{
    protected $fillable = [
        'meal_id', 'product_id', 'free_text', 'grams', 'servings',
        'calories', 'proteins', 'fats', 'carbs'
    ];
    
    protected $casts = [
        'grams' => 'decimal:2',
        'servings' => 'decimal:2',
        'calories' => 'decimal:2',
        'proteins' => 'decimal:2',
        'fats' => 'decimal:2',
        'carbs' => 'decimal:2'
    ];
    
    // Отношения
    public function meal(): BelongsTo
    public function product(): BelongsTo
    
    // Вычисляемые атрибуты
    public function getProductNameAttribute(): string
    public function getWeightAttribute(): ?float
    public function getPortionsAttribute(): ?float
    public function isFreeText(): bool
    public function isFromDatabase(): bool
}
```

### Meal (обновлённая)
```php
class Meal extends Model
{
    // Новые отношения
    public function items(): HasMany
    
    // Новые атрибуты
    public function getTotalCaloriesAttribute(): float
    public function getTotalProteinsAttribute(): float
    public function getTotalFatsAttribute(): float
    public function getTotalCarbsAttribute(): float
    public function hasItems(): bool
    public function getItemsCountAttribute(): int
}
```

## 🛡️ Безопасность

### Проверки доступа:
- Все операции проверяют `user_id` приёма пищи
- Пользователь может управлять только своими приёмами
- Foreign key constraints обеспечивают целостность данных

### Валидация:
- `product_id` или `free_text` обязателен
- `grams` и `servings` не могут быть указаны одновременно
- Все числовые значения имеют ограничения (min/max)

## 📝 Логирование

Все операции логируются с деталями:
- Создание/обновление/удаление элементов
- Ошибки валидации и доступа
- Пересчёт БЖУ/калорий

## 🎯 Примеры использования

### Создание завтрака с несколькими продуктами:
1. **Создать приём:** `POST /meals`
2. **Добавить овсянку:** `POST /meals/{id}/items` с `product_id: 123, grams: 150`
3. **Добавить банан:** `POST /meals/{id}/items` с `product_id: 456, grams: 120`
4. **Добавить молоко:** `POST /meals/{id}/items` с `product_id: 789, grams: 200`
5. **Получить итоги:** `GET /meals/{id}` - автоматически посчитает общие БЖУ/калории

### Редактирование порции:
1. **Изменить вес овсянки:** `PATCH /meals/{id}/items/{item_id}` с `grams: 200`
2. **Система автоматически пересчитает** калории, белки, жиры, углеводы

## 🔧 Технические детали

### Миграция:
```bash
php artisan migrate
```

### Кэширование:
- Агрегаты рассчитываются в реальном времени
- Можно добавить кэширование для оптимизации

### Производительность:
- Индексы на `meal_id` и `product_id`
- Eager loading для `items.product`
- Batch операции для массового добавления

---

**Версия:** 1.0  
**Дата:** 2025-08-13  
**Автор:** Fizio Development Team
