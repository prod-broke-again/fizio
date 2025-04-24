# Тестирование API Fizio

В этом руководстве описаны различные способы тестирования API приложения Fizio.

## 1. Ручное тестирование с помощью Bash-скрипта

Скрипт `test_api.sh` позволяет быстро протестировать все основные эндпоинты API с помощью curl.

### Запуск скрипта:

```bash
# Сделайте скрипт исполняемым
chmod +x test_api.sh

# Запустите скрипт
./test_api.sh
```

Скрипт последовательно выполнит следующие операции:
1. Регистрация пользователя
2. Вход в систему
3. Получение профиля пользователя
4. Сохранение цели фитнеса
5. Получение текущей цели фитнеса
6. Выход из системы

Для запуска требуется установленный `jq` (для форматирования JSON):
```bash
sudo apt install jq
```

## 2. Тестирование с помощью PHPUnit

В проекте есть два файла с автоматическими тестами:
- `tests/Feature/AuthTest.php` - тесты аутентификации
- `tests/Feature/UserTest.php` - тесты функций пользователя

### Запуск тестов:

```bash
# Запуск всех тестов
php artisan test

# Запуск только тестов аутентификации
php artisan test --filter=AuthTest

# Запуск только тестов пользователя
php artisan test --filter=UserTest
```

## 3. Тестирование с помощью Postman

В проекте есть файл коллекции Postman `fizio_api_collection.json`, который вы можете импортировать в Postman для тестирования API.

### Настройка Postman:

1. Откройте Postman
2. Нажмите "Import" и выберите файл `fizio_api_collection.json`
3. Создайте новое окружение с переменной `base_url` равной URL вашего API (например, `https://fizio.online/api`)
4. После успешной авторизации токен будет автоматически сохранен в переменной окружения `auth_token` и использован для последующих запросов

## 4. Ручное тестирование с помощью curl

### Регистрация:
```bash
curl -X POST https://fizio.online/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Тестовый Пользователь",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "gender": "male",
    "device_token": "test-device-token"
  }'
```

### Авторизация:
```bash
curl -X POST https://fizio.online/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "device_token": "test-device-token"
  }'
```

### Получение профиля (используйте полученный token):
```bash
curl -X GET https://fizio.online/api/user/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### Сохранение цели фитнеса:
```bash
curl -X POST https://fizio.online/api/user/fitness-goal \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "goal": "weight-loss"
  }'
```

### Получение цели фитнеса:
```bash
curl -X GET https://fizio.online/api/user/fitness-goal \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### Выход из системы:
```bash
curl -X POST https://fizio.online/api/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

## Примечания по тестированию

- При тестировании регистрации используйте уникальный email каждый раз
- Убедитесь, что все заголовки указаны правильно, особенно `Content-Type` и `Authorization`
- При ручном тестировании с помощью curl копируйте токен из ответа авторизации для использования в последующих запросах 