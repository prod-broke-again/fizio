# API Документация: Spoonacular (fizio.online)

Интеграция с Spoonacular API через бэкенд `fizio.online` предоставляет доступ к обширной базе данных продуктов питания.

**Базовый URL API:** `https://fizio.online/api/spoonacular`

Все запросы требуют действительного токена авторизации (Bearer token), если иное не указано.

---

## 1. Поиск продуктов

Позволяет искать продукты по текстовому запросу.

- **Эндпоинт:** `GET /products/search`
- **Метод:** `GET`

### Параметры запроса (Query Parameters):

| Параметр   | Тип     | Обязательность | Описание                                                                 |
|------------|---------|----------------|--------------------------------------------------------------------------|
| `query`    | string  | Да             | Текстовый запрос для поиска продуктов (например, "apple", "молоко"). Минимум 2 символа. |
| `page`     | integer | Нет            | Номер страницы результатов. По умолчанию: `1`.                           |
| `per_page` | integer | Нет            | Количество продуктов на странице. По умолчанию: `20`. Максимум: `50`.       |

### Пример запроса:

`https://fizio.online/api/spoonacular/products/search?query=молоко&page=1&per_page=10`

### Пример успешного ответа (200 OK):

```json
{
  "products": [
    {
      "id": 649490,
      "title": "Молоко",
      "image": "https://spoonacular.com/productImages/649490-312x231.jpg",
      "imageType": "jpg"
    },
    {
      "id": 1083727,
      "title": "Молоко коровье",
      "image": "https://spoonacular.com/productImages/1083727-312x231.jpg",
      "imageType": "jpg"
    }
    // ... другие продукты
  ],
  "total": 125 // Общее количество найденных продуктов
}
```

### Пример ответа при ошибке валидации (422 Unprocessable Entity):

```json
{
  "error": "Ошибка валидации",
  "messages": {
    "query": [
      "Поле query обязательно для заполнения.",
      "Количество символов в поле query должно быть не менее 2."
    ]
  }
}
```

---

## 2. Получение информации о продукте по UPC (штрих-коду)

Позволяет получить детальную информацию о продукте, используя его UPC (Universal Product Code) - штрих-код.

- **Эндпоинт:** `GET /products/upc/{upc}`
- **Метод:** `GET`

### Параметры пути (Path Parameters):

| Параметр | Тип    | Обязательность | Описание                             |
|----------|--------|----------------|--------------------------------------|
| `upc`    | string | Да             | UPC (штрих-код) продукта.            |

### Пример запроса:

`https://fizio.online/api/spoonacular/products/upc/049000050103`

### Пример успешного ответа (200 OK):

```json
{
  "id": 22347,
  "title": "Sprite",
  "badges": [
    "pride_badge"
  ],
  "importantBadges": [],
  "breadcrumbs": [
    "Sprite",
    "soda"
  ],
  "generatedText": null,
  "imageType": "png",
  "ingredientCount": 0,
  "ingredientList": "",
  "ingredients": [],
  "likes": 0,
  "nutrition": {
    "nutrients": [
      {
        "name": "Calories",
        "amount": 140.0,
        "unit": "kcal",
        "percentOfDailyNeeds": 7.0
      }
      // ... другие нутриенты
    ],
    "caloricBreakdown": {
      "percentProtein": 0.0,
      "percentFat": 0.0,
      "percentCarbs": 100.0
    },
    "calories": 140.0,
    "fat": "0g",
    "protein": "0g",
    "carbs": "38g"
  },
  // ... другая информация о продукте
}
```

### Пример ответа при ошибке (404 Not Found или 500 Internal Server Error):

```json
{
  "error": "Ошибка получения информации о продукте", // или "Product not found or API error"
  "message": "Продукт не найден" // опционально, в зависимости от ошибки
}
```

---

## 3. Получение детальной информации о продукте по ID

Позволяет получить полную информацию о продукте, включая пищевую ценность, используя его внутренний ID Spoonacular (полученный из эндпоинта поиска).

- **Эндпоинт:** `GET /products/{id}/information`
- **Метод:** `GET`

### Параметры пути (Path Parameters):

| Параметр | Тип     | Обязательность | Описание                                      |
|----------|---------|----------------|-----------------------------------------------|
| `id`     | integer | Да             | ID продукта Spoonacular (например, `649490`). |

### Пример запроса:

`https://fizio.online/api/spoonacular/products/649490/information`

### Пример успешного ответа (200 OK):

```json
{
  "id": 649490,
  "title": "Молоко",
  "image": "https://spoonacular.com/productImages/649490-312x231.jpg",
  "imageType": "jpg",
  "servings": {
    "number": 1.0,
    "size": 240.0,
    "unit": "ml"
  },
  "badges": [],
  "ingredientCount": 1,
  "ingredientList": "Молоко",
  "ingredients": [
    {
      "name": "молоко",
      "safety_level": null,
      "description": null
    }
  ],
  "nutrition": {
    "nutrients": [
      {
        "name": "Calories",
        "amount": 103.0,
        "unit": "kcal",
        "percentOfDailyNeeds": 5.15
      },
      {
        "name": "Protein",
        "amount": 8.0,
        "unit": "g",
        "percentOfDailyNeeds": 16.0
      },
      {
        "name": "Fat",
        "amount": 2.4,
        "unit": "g",
        "percentOfDailyNeeds": 3.69
      },
      {
        "name": "Carbohydrates",
        "amount": 12.0,
        "unit": "g",
        "percentOfDailyNeeds": 4.0
      }
      // ... другие нутриенты
    ],
    "caloricBreakdown": {
      "percentProtein": 29.01,
      "percentFat": 20.07,
      "percentCarbs": 50.92
    },
    "calories": 103.0,
    "fat": "2.4g",
    "protein": "8g",
    "carbs": "12g"
  },
  // ... другая информация о продукте
}
```

### Пример ответа при ошибке (404 Not Found или 500 Internal Server Error):

```json
{
  "error": "Product not found or API error" // или "Internal Server Error"
}
``` 