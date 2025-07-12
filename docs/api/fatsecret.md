# FatSecret API (fizio.online)

Интеграция с FatSecret API через backend `fizio.online` предоставляет расширенные возможности работы с продуктами питания, брендами, категориями и рецептами, а также распознавание продуктов по фото.

**Базовый URL API:** `/api/fatsecret`

Все запросы требуют действительного Bearer токена авторизации.

**Важно:** Некоторые методы, такие как "Автозаполнение продуктов" (autocomplete) и "Распознавание продуктов по фото" (image recognition), требуют `premier` scope от FatSecret. Сервис `fizio.online` автоматически запрашивает этот scope при получении токена доступа к FatSecret API, поэтому для конечного пользователя API `fizio.online` никаких дополнительных действий не требуется, кроме наличия валидного токена авторизации `fizio.online`.

---

## 1. Поиск продуктов

- **Эндпоинт:** `GET /api/fatsecret/foods/search`
- **Параметры:**
  - `query` (string, обяз.) — поисковый запрос (минимум 2 символа)
  - `page` (integer, не обяз.) — номер страницы (по умолчанию 1)
  - `max_results` (integer, не обяз.) — количество на странице (по умолчанию 20, максимум 50)

**Пример:**
```
GET /api/fatsecret/foods/search?query=молоко&page=1&max_results=10
```

---

## 2. Получение информации о продукте

- **Эндпоинт:** `GET /api/fatsecret/foods/{foodId}`
- **Параметры:**
  - `foodId` (string, обяз.) — ID продукта

**Пример:**
```
GET /api/fatsecret/foods/12345
```

---

## 3. Автозаполнение продуктов (autocomplete)

- **Эндпоинт:** `GET /api/fatsecret/foods/autocomplete`
- **Параметры:**
  - `query` (string, обяз.) — поисковый запрос (минимум 2 символа)
- **Примечание:** Этот метод требует `premier` scope (обрабатывается на стороне сервера `fizio.online`).

**Пример:**
```
GET /api/fatsecret/foods/autocomplete?query=молоко
```

---

## 4. Распознавание продуктов по фото (image recognition)

- **Эндпоинт:** `POST /api/fatsecret/foods/recognize`
- **Параметры:**
  - `image` (file, обяз.) — изображение (jpg/png/webp, до 1.09MB)
- **Примечание:** Этот метод требует `premier` scope (обрабатывается на стороне сервера `fizio.online`).

**Пример запроса (multipart/form-data):**
```