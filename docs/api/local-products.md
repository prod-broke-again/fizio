# API Локальных Продуктов

В этом документе описаны API для работы с локальной базой данных продуктов Open Food Facts. Эти API предоставляют быстрый доступ к импортированным данным без зависимости от внешних сервисов.

## Обзор

Локальные продукты API используют данные, импортированные из Open Food Facts в локальную базу данных. Это обеспечивает:
- **Быстрый доступ** - нет зависимости от внешних API
- **Надежность** - данные всегда доступны
- **Фильтрацию** - поддержка товаров СНГ и России
- **Кэширование** - результаты кэшируются для повышения производительности

## Поиск продуктов

### Поиск по названию, бренду, категориям
```http
GET /api/local-products/search
```

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| query | string | Да | Поисковый запрос (минимум 2 символа) |
| page | integer | Нет | Номер страницы (по умолчанию: 1) |
| per_page | integer | Нет | Количество результатов на странице (по умолчанию: 20, максимум: 50) |

#### Пример запроса
```bash
curl "http://localhost:9080/api/local-products/search?query=россия&page=1&per_page=10"
```

#### Пример ответа
```json
{
    "products": [
        {
            "id": 1,
            "code": "0099990000005",
            "product_name": "Actimel Малина и клюква",
            "brands": "Actimel",
            "categories": "Молочные продукты",
            "countries": "en:Russia",
            "nutriscore_grade": "a",
            "nova_group": "1",
            "unique_scans_n": 150,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    ],
    "total": 28,
    "current_page": 1,
    "per_page": 10,
    "last_page": 3
}
```

## Поиск по штрих-коду

### Получение продукта по штрих-коду
```http
GET /api/local-products/barcode/{barcode}
```

#### Параметры пути
| Параметр | Тип | Описание |
|----------|-----|-----------|
| barcode | string | Штрих-код продукта |

#### Пример запроса
```bash
curl "http://localhost:9080/api/local-products/barcode/0099990000005"
```

#### Пример ответа
```json
{
    "id": 1,
    "code": "0099990000005",
    "product_name": "Actimel Малина и клюква",
    "brands": "Actimel",
    "generic_name": "Йогурт с малиной и клюквой",
    "categories": "Молочные продукты, Йогурты",
    "countries": "en:Russia",
    "ingredients_text": "Молоко, сахар, малина, клюква, закваска",
    "allergens": "Молоко",
    "nutriscore_grade": "a",
    "nova_group": "1",
    "unique_scans_n": 150,
    "image_url": "https://images.openfoodfacts.org/images/products/009/999/000/0005/front_ru.1.400.jpg",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

## Фильтрация по регионам

### Товары СНГ
```http
GET /api/local-products/cis
```

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| page | integer | Нет | Номер страницы (по умолчанию: 1) |
| per_page | integer | Нет | Количество результатов на странице (по умолчанию: 20, максимум: 50) |

#### Пример запроса
```bash
curl "http://localhost:9080/api/local-products/cis?page=1&per_page=10"
```

#### Пример ответа
```json
{
    "products": [
        {
            "id": 1,
            "code": "0099990000005",
            "product_name": "Actimel Малина и клюква",
            "brands": "Actimel",
            "countries": "en:Russia",
            "unique_scans_n": 150
        }
    ],
    "total": 20,
    "current_page": 1,
    "per_page": 10,
    "last_page": 2
}
```

### Российские товары
```http
GET /api/local-products/russian
```

#### Параметры запроса
| Параметр | Тип | Обязательный | Описание |
|----------|-----|--------------|-----------|
| page | integer | Нет | Номер страницы (по умолчанию: 1) |
| per_page | integer | Нет | Количество результатов на странице (по умолчанию: 20, максимум: 50) |

#### Пример запроса
```bash
curl "http://localhost:9080/api/local-products/russian?page=1&per_page=10"
```

## Статистика

### Получение статистики базы данных
```http
GET /api/local-products/stats
```

#### Пример запроса
```bash
curl "http://localhost:9080/api/local-products/stats"
```

#### Пример ответа
```json
{
    "total_products": 28835,
    "cis_products": 20,
    "russian_products": 20,
    "last_import": "2024-01-15T10:30:00.000000Z"
}
```

## Структура данных продукта

### Основные поля
| Поле | Тип | Описание |
|------|-----|----------|
| id | integer | Уникальный идентификатор |
| code | string | Штрих-код продукта |
| product_name | string | Название продукта |
| brands | string | Бренд/производитель |
| generic_name | string | Общее название |
| categories | string | Категории продукта |
| countries | string | Страны продажи |
| ingredients_text | string | Состав продукта |
| allergens | string | Аллергены |
| nutriscore_grade | string | Оценка Nutri-Score (a-e) |
| nova_group | string | Группа NOVA (1-4) |
| unique_scans_n | integer | Количество сканирований |
| image_url | string | URL изображения продукта |
| created_at | datetime | Дата создания записи |
| updated_at | datetime | Дата обновления записи |

### Дополнительные поля
| Поле | Тип | Описание |
|------|-----|----------|
| quantity | string | Количество/вес |
| packaging | string | Упаковка |
| manufacturing_places | string | Места производства |
| origins | string | Происхождение |
| traces | string | Следы аллергенов |
| additives | string | Добавки |
| labels | string | Этикетки |

## Кэширование

### Настройки кэширования
- **Поиск**: 1 час (3600 секунд)
- **Продукт по штрих-коду**: 24 часа (86400 секунд)
- **Фильтры по регионам**: 1 час (3600 секунд)

### Ключи кэша
- `local_products_search_{query}_{page}_{per_page}`
- `local_products_barcode_{barcode}`
- `local_products_cis_{page}_{per_page}`
- `local_products_russian_{page}_{per_page}`

## Обработка ошибок

### Формат ошибок
```json
{
    "error": "Описание ошибки",
    "message": "Детальное сообщение об ошибке"
}
```

### Коды ошибок
- **422**: Ошибка валидации (некорректные параметры)
- **500**: Внутренняя ошибка сервера

### Типичные ошибки
```json
{
    "error": "Ошибка валидации",
    "messages": {
        "query": ["Поисковый запрос должен содержать минимум 2 символа"]
    }
}
```

## Ограничения

### Лимиты
- Минимальная длина поискового запроса: 2 символа
- Максимальное количество результатов на страницу: 50
- Максимальная длина штрих-кода: 50 символов

### Производительность
- Рекомендуется использовать пагинацию для больших результатов
- Кэширование автоматически применяется для всех запросов
- Индексы оптимизированы для быстрого поиска

## Примеры использования

### JavaScript
```javascript
// Поиск продуктов
const searchProducts = async (query) => {
    const response = await fetch(`/api/local-products/search?query=${encodeURIComponent(query)}`);
    const data = await response.json();
    return data;
};

// Поиск по штрих-коду
const getProductByBarcode = async (barcode) => {
    const response = await fetch(`/api/local-products/barcode/${barcode}`);
    const data = await response.json();
    return data;
};

// Получение российских товаров
const getRussianProducts = async (page = 1) => {
    const response = await fetch(`/api/local-products/russian?page=${page}`);
    const data = await response.json();
    return data;
};
```

### PHP
```php
// Поиск продуктов
$response = Http::get('/api/local-products/search', [
    'query' => 'россия',
    'page' => 1,
    'per_page' => 20
]);

// Поиск по штрих-коду
$product = Http::get('/api/local-products/barcode/0099990000005');

// Статистика
$stats = Http::get('/api/local-products/stats');
```

## Сравнение с внешними API

| Аспект | Локальные продукты | OpenFoodFacts API |
|--------|-------------------|-------------------|
| Скорость | Очень быстро | Зависит от внешнего API |
| Надежность | Высокая | Зависит от внешнего сервиса |
| Данные | Только импортированные | Все доступные данные |
| Фильтрация | СНГ, Россия | Глобальная |
| Кэширование | Автоматическое | Настроено вручную |

## Рекомендации

1. **Используйте локальные продукты для:**
   - Быстрого поиска популярных товаров
   - Работы с товарами СНГ и России
   - Приложений, требующих высокой надежности

2. **Используйте внешние API для:**
   - Поиска новых товаров
   - Глобального поиска
   - Получения актуальной информации 