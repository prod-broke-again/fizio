# API: Недельный план (Week Plan)

Все эндпоинты требуют аутентификации (`auth:sanctum`).

Базовый URL: `/api/week-plan`

## 1. Получение недельного плана на текущую неделю

-   **Эндпоинт:** `GET /api/week-plan`
-   **Описание:** Возвращает записи недельного плана для аутентифицированного пользователя за текущую неделю (с понедельника по воскресенье).
-   **Параметры запроса:** Нет.
-   **Успешный ответ (200 OK):**
    Массив объектов `WeekPlan`. Каждый объект включает:
    ```json
    [
        {
            "id": 1, // ID записи WeekPlan
            "user_id": "uuid-пользователя",
            "date": "YYYY-MM-DD",
            "meals": [ // Массив приемов пищи, добавленных в этот день плана
                {
                    "id": 1, // Локальный ID приема пищи в рамках этого дня плана
                    "name": "Завтрак",
                    "calories": 300,
                    "type": "breakfast",
                    "time": "08:00",
                    "completed": false
                }
            ],
            "workouts": [ // Массив тренировок, добавленных в этот день плана
                {
                    "id": 1, // Локальный ID тренировки в рамках этого дня плана
                    "workout_id": "uuid-тренировки-из-таблицы-workouts",
                    "completed": false
                }
            ],
            "progress": 0, // Общий процент выполнения для этого дня (0-100)
            "created_at": "ГГГГ-ММ-ДДTЧЧ:ММ:СС.SSSSSSZ",
            "updated_at": "ГГГГ-ММ-ДДTЧЧ:ММ:СС.SSSSSSZ"
        }
        // ... другие дни плана
    ]
    ```

## 2. Получение недельного плана на конкретную дату

-   **Эндпоинт:** `GET /api/week-plan/{date}`
-   **Описание:** Возвращает запись недельного плана для аутентифицированного пользователя на указанную дату.
-   **Параметры пути:**
    -   `{date}` (string, required): Дата в формате `YYYY-MM-DD`.
-   **Успешный ответ (200 OK):**
    Объект `WeekPlan` (структура аналогична элементу массива из п.1).
-   **Ошибка (404 Not Found):** Если план на указанную дату не найден.

## 3. Добавление приема пищи в план на дату

-   **Эндпоинт:** `POST /api/week-plan/{date}/meal`
-   **Описание:** Добавляет новый прием пищи в недельный план на указанную дату. Если плана на эту дату еще не существует, он будет создан.
-   **Параметры пути:**
    -   `{date}` (string, required): Дата в формате `YYYY-MM-DD`.
-   **Тело запроса (JSON):**
    ```json
    {
        "name": "Обед",
        "calories": 600,
        "type": "lunch",
        "time": "13:00"
    }
    ```
    -   `name` (string, required, max:255): Название приема пищи.
    -   `calories` (integer, required, min:0): Количество калорий.
    -   `type` (string, required, in:breakfast,lunch,dinner,snack): Тип приема пищи.
    -   `time` (string, required, format:H:i): Время приема пищи (например, "14:30").
-   **Успешный ответ (200 OK):**
    Обновленный объект `WeekPlan` для указанной даты.
-   **Ошибка (422 Unprocessable Entity):** Если данные в теле запроса не прошли валидацию.

## 4. Добавление тренировки в план на дату

-   **Эндпоинт:** `POST /api/week-plan/{date}/workout`
-   **Описание:** Добавляет существующую тренировку (по ее ID) в недельный план на указанную дату. Если плана на эту дату еще не существует, он будет создан.
-   **Параметры пути:**
    -   `{date}` (string, required): Дата в формате `YYYY-MM-DD`.
-   **Тело запроса (JSON):**
    ```json
    {
        "workout_id": "uuid-существующей-тренировки"
    }
    ```
    -   `workout_id` (string, required, uuid, exists:workouts,id): UUID существующей тренировки из таблицы `workouts`.
-   **Успешный ответ (200 OK):**
    Обновленный объект `WeekPlan` для указанной даты.
-   **Ошибка (422 Unprocessable Entity):** Если данные в теле запроса не прошли валидацию (например, `workout_id` не найден или не UUID).

## 5. Обновление прогресса выполнения для дня плана

-   **Эндпоинт:** `PATCH /api/week-plan/{date}/progress`
-   **Описание:** Обновляет общий процент выполнения для указанного дня в недельном плане. (Примечание: это поле также автоматически пересчитывается при добавлении/отметке выполнения приемов пищи/тренировок через `WeekPlanService`).
-   **Параметры пути:**
    -   `{date}` (string, required): Дата в формате `YYYY-MM-DD`.
-   **Тело запроса (JSON):**
    ```json
    {
        "progress": 75
    }
    ```
    -   `progress` (integer, required, min:0, max:100): Процент выполнения.
-   **Успешный ответ (200 OK):**
    Обновленный объект `WeekPlan`.
-   **Ошибка (404 Not Found):** Если план на указанную дату не найден.
-   **Ошибка (422 Unprocessable Entity):** Если данные в теле запроса не прошли валидацию.

## 6. Отметка элемента плана как выполненного/невыполненного (Toggle)

-   **Эндпоинт:** `PATCH /api/week-plan/{date}/{type}/{id}/toggle-complete`
-   **Описание:** Изменяет статус выполнения (выполнено/не выполнено) для приема пищи или тренировки в рамках дня недельного плана.
-   **Параметры пути:**
    -   `{date}` (string, required): Дата в формате `YYYY-MM-DD`.
    -   `{type}` (string, required): Тип элемента. Допустимые значения: `meal` или `workout`.
    -   `{id}` (integer, required): Локальный ID приема пищи или тренировки внутри JSON-массива (`meals` или `workouts`) данного дня плана. Этот ID присваивается автоматически при добавлении элемента в план.
-   **Тело запроса:** Пустое.
-   **Успешный ответ (200 OK):**
    Обновленный объект `WeekPlan`.
-   **Ошибка (404 Not Found):** Если план на указанную дату не найден.
-   **Ошибка (Прочие):** Если элемент с указанным `{type}` и `{id}` не найден в плане.

---

**Структура объекта `WeekPlan` (возвращается в ответах):**

```json
{
    "id": 1, // ID записи WeekPlan в базе данных
    "user_id": "uuid-пользователя",
    "date": "YYYY-MM-DD", // Дата, к которой относится этот план
    "meals": [ // Массив объектов приемов пищи
        {
            "id": 1, // Локальный ID (для отметки выполнения)
            "name": "Омлет с овощами",
            "calories": 450,
            "type": "breakfast", // "breakfast", "lunch", "dinner", "snack"
            "time": "09:00",
            "completed": false // true, если выполнен
        }
        // ... другие приемы пищи
    ],
    "workouts": [ // Массив объектов запланированных тренировок
        {
            "id": 1, // Локальный ID (для отметки выполнения)
            "workout_id": "uuid-существующей-тренировки-из-таблицы-workouts",
            "completed": false // true, если выполнен
        }
        // ... другие тренировки
    ],
    "progress": 50, // Общий процент выполнения для этого дня (0-100)
    "created_at": "ГГГГ-ММ-ДДTЧЧ:ММ:СС.SSSSSSZ",
    "updated_at": "ГГГГ-ММ-ДДTЧЧ:ММ:СС.SSSSSSZ"
}
``` 