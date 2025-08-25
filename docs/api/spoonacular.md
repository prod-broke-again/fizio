# Spoonacular API Documentation

## Обзор

Spoonacular API предоставляет доступ к обширной базе данных рецептов, ингредиентов, продуктов питания и инструментам для планирования питания.

## Базовый URL

```
https://api.spoonacular.com
```

## Аутентификация

Все запросы требуют API ключ, который передается как параметр `apiKey` в URL или в заголовке `x-api-key`.

## Продукты

### Поиск продуктов

**GET** `/api/spoonacular/products/search`

Поиск продуктов питания в базе данных Spoonacular.

**Параметры:**
- `query` (обязательный) - строка поиска
- `page` (опционально) - номер страницы (по умолчанию: 1)
- `per_page` (опционально) - количество результатов на странице (по умолчанию: 20, максимум: 50)

**Пример запроса:**
```bash
GET /api/spoonacular/products/search?query=apple&page=1&per_page=10
```

### Поиск продукта по UPC

**GET** `/api/spoonacular/products/upc/{upc}`

Получение информации о продукте по UPC коду.

**Параметры:**
- `upc` (обязательный) - UPC код продукта

**Пример запроса:**
```bash
GET /api/spoonacular/products/upc/041190000000
```

### Информация о продукте

**GET** `/api/spoonacular/products/{id}/information`

Получение детальной информации о продукте по ID.

**Параметры:**
- `id` (обязательный) - ID продукта

**Пример запроса:**
```bash
GET /api/spoonacular/products/12345/information
```

## Рецепты

### Поиск рецептов

**GET** `/api/spoonacular/recipes/search`

Поиск рецептов с различными фильтрами.

**Параметры:**
- `query` (обязательный) - строка поиска
- `number` (опционально) - количество результатов (1-100, по умолчанию: 10)
- `addRecipeNutrition` (опционально) - включить информацию о питании (по умолчанию: true)
- `instructionsRequired` (опционально) - только рецепты с инструкциями (по умолчанию: true)
- `diet` (опционально) - диета (gluten-free, ketogenic, vegetarian, etc.)
- `cuisine` (опционально) - кухня (italian, mexican, asian, etc.)
- `intolerances` (опционально) - непереносимости (dairy, egg, gluten, etc.)
- `maxReadyTime` (опционально) - максимальное время приготовления в минутах
- `minProtein`, `maxProtein` (опционально) - диапазон белка в граммах
- `minFat`, `maxFat` (опционально) - диапазон жиров в граммах
- `minCarbs`, `maxCarbs` (опционально) - диапазон углеводов в граммах

**Пример запроса:**
```bash
GET /api/spoonacular/recipes/search?query=pasta&diet=vegetarian&maxReadyTime=30&number=5
```

### Поиск рецептов по ингредиентам

**GET** `/api/spoonacular/recipes/by-ingredients`

Поиск рецептов, которые можно приготовить из имеющихся ингредиентов.

**Параметры:**
- `ingredients` (обязательный) - массив ингредиентов
- `number` (опционально) - количество результатов (1-100, по умолчанию: 10)
- `ranking` (опционально) - тип ранжирования (1: максимизировать использованные ингредиенты, 2: минимизировать недостающие ингредиенты)
- `ignorePantry` (опционально) - игнорировать базовые ингредиенты (по умолчанию: true)

**Пример запроса:**
```bash
GET /api/spoonacular/recipes/by-ingredients?ingredients[]=tomato&ingredients[]=cheese&ingredients[]=pasta&number=5
```

### Информация о рецепте

**GET** `/api/spoonacular/recipes/{recipeId}/information`

Получение детальной информации о рецепте.

**Параметры:**
- `recipeId` (обязательный) - ID рецепта
- `includeNutrition` (опционально) - включить информацию о питании (по умолчанию: true)

**Пример запроса:**
```bash
GET /api/spoonacular/recipes/716429/information?includeNutrition=true
```

### Случайные рецепты

**GET** `/api/spoonacular/recipes/random`

Получение случайных рецептов.

**Параметры:**
- `number` (опционально) - количество рецептов (1-100, по умолчанию: 10)
- `addRecipeNutrition` (опционально) - включить информацию о питании (по умолчанию: true)
- `tags` (опционально) - теги для фильтрации
- `diet` (опционально) - диета
- `cuisine` (опционально) - кухня
- `intolerances` (опционально) - непереносимости

**Пример запроса:**
```bash
GET /api/spoonacular/recipes/random?number=5&diet=vegetarian&cuisine=italian
```

## Ингредиенты

### Поиск ингредиентов

**GET** `/api/spoonacular/ingredients/search`

Поиск ингредиентов в базе данных.

**Параметры:**
- `query` (обязательный) - строка поиска
- `number` (опционально) - количество результатов (1-100, по умолчанию: 10)
- `addChildren` (опционально) - включить дочерние ингредиенты (по умолчанию: true)
- `metaInformation` (опционально) - включить мета-информацию
- `sortDirection` (опционально) - направление сортировки (asc/desc)

**Пример запроса:**
```bash
GET /api/spoonacular/ingredients/search?query=apple&number=10
```

### Информация об ингредиенте

**GET** `/api/spoonacular/ingredients/{ingredientId}/information`

Получение детальной информации об ингредиенте.

**Параметры:**
- `ingredientId` (обязательный) - ID ингредиента
- `amount` (опционально) - количество ингредиента
- `unit` (опционально) - единица измерения

**Пример запроса:**
```bash
GET /api/spoonacular/ingredients/9003/information?amount=100&unit=grams
```

### Автодополнение ингредиентов

**GET** `/api/spoonacular/ingredients/autocomplete`

Автодополнение поиска ингредиентов.

**Параметры:**
- `query` (обязательный) - строка поиска
- `number` (опционально) - количество результатов (1-100, по умолчанию: 10)

**Пример запроса:**
```bash
GET /api/spoonacular/ingredients/autocomplete?query=app&number=5
```

## Планирование питания

### Генерация плана питания

**GET** `/api/spoonacular/meal-planner/generate`

Генерация плана питания на день или неделю.

**Параметры:**
- `timeFrame` (опционально) - период планирования (day/week, по умолчанию: day)
- `targetCalories` (опционально) - целевые калории (200-8000, по умолчанию: 2000)
- `diet` (опционально) - диета
- `exclude` (опционально) - исключаемые ингредиенты

**Пример запроса:**
```bash
GET /api/spoonacular/meal-planner/generate?timeFrame=week&targetCalories=1800&diet=vegetarian
```

### План питания на неделю

**GET** `/api/spoonacular/meal-planner/week`

Получение плана питания на неделю для пользователя.

**Параметры:**
- `username` (обязательный) - имя пользователя
- `hash` (обязательный) - хеш для аутентификации

**Пример запроса:**
```bash
GET /api/spoonacular/meal-planner/week?username=john&hash=abc123
```

## Анализ питания

### Анализ рецепта

**POST** `/api/spoonacular/recipes/analyze`

Анализ рецепта для получения информации о питании.

**Параметры:**
- `title` (обязательный) - название блюда
- `ingredients` (обязательный) - список ингредиентов
- `instructions` (опционально) - инструкции по приготовлению

**Пример запроса:**
```bash
POST /api/spoonacular/recipes/analyze
Content-Type: application/json

{
    "title": "Spaghetti Carbonara",
    "ingredients": "spaghetti, eggs, bacon, parmesan cheese, black pepper",
    "instructions": "Cook pasta, mix with eggs and cheese, add bacon"
}
```

### Оценка питательной ценности по названию

**GET** `/api/spoonacular/recipes/guess-nutrition`

Оценка питательной ценности блюда по его названию.

**Параметры:**
- `title` (обязательный) - название блюда

**Пример запроса:**
```bash
GET /api/spoonacular/recipes/guess-nutrition?title=Spaghetti Carbonara
```

### Классификация кухни

**POST** `/api/spoonacular/recipes/classify-cuisine`

Определение типа кухни по названию и ингредиентам.

**Параметры:**
- `title` (обязательный) - название блюда
- `ingredients` (обязательный) - список ингредиентов

**Пример запроса:**
```bash
POST /api/spoonacular/recipes/classify-cuisine
Content-Type: application/json

{
    "title": "Spaghetti Carbonara",
    "ingredients": "spaghetti, eggs, bacon, parmesan cheese, black pepper"
}
```

## Поддерживаемые диеты

- `gluten-free` - безглютеновая
- `ketogenic` - кетогенная
- `vegetarian` - вегетарианская
- `lacto-vegetarian` - лакто-вегетарианская
- `ovo-vegetarian` - ово-вегетарианская
- `vegan` - веганская
- `pescetarian` - пескетарианская
- `paleo` - палео
- `primal` - примальная
- `low-fodmap` - низкофодмап
- `whole30` - Whole30

## Поддерживаемые кухни

- `african` - африканская
- `american` - американская
- `british` - британская
- `cajun` - каджунская
- `caribbean` - карибская
- `chinese` - китайская
- `eastern european` - восточноевропейская
- `european` - европейская
- `french` - французская
- `german` - немецкая
- `greek` - греческая
- `indian` - индийская
- `irish` - ирландская
- `italian` - итальянская
- `japanese` - японская
- `jewish` - еврейская
- `korean` - корейская
- `latin american` - латиноамериканская
- `mediterranean` - средиземноморская
- `mexican` - мексиканская
- `middle eastern` - ближневосточная
- `nordic` - скандинавская
- `southern` - южная
- `spanish` - испанская
- `thai` - тайская
- `vietnamese` - вьетнамская

## Коды ответов

- `200` - Успешный запрос
- `400` - Неверный запрос
- `401` - Неавторизованный доступ
- `402` - Превышен лимит запросов
- `404` - Ресурс не найден
- `429` - Слишком много запросов
- `500` - Внутренняя ошибка сервера

## Ограничения

- Бесплатный план: 150 запросов в день
- Лимит запросов: 60 запросов в минуту
- Максимальное количество результатов: 100 на запрос 