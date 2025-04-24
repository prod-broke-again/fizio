#!/bin/bash

# Установка базового URL
API_URL="https://fizio.online/api"
TOKEN=""

# Цвета для вывода
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}API Тесты для Fizio App${NC}"
echo "=================================="

# Функция для отображения ответов
function display_response {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}Статус: Успешно ($2)${NC}"
    else
        echo -e "${RED}Статус: Ошибка ($2)${NC}"
    fi
    echo "$3" | jq .
    echo "=================================="
}

# Тест 1: Регистрация пользователя
echo -e "${BLUE}Тест 1: Регистрация пользователя${NC}"
RESPONSE=$(curl -s -X POST "$API_URL/auth/register" \
    -H "Content-Type: application/json" \
    -d '{
        "name": "Тестовый Пользователь",
        "email": "test@example.com",
        "password": "password123",
        "password_confirmation": "password123",
        "gender": "male",
        "device_token": "test-device-token"
    }')
STATUS=$?
HTTP_CODE=$(echo "$RESPONSE" | jq -r '.success')
display_response $STATUS $HTTP_CODE "$RESPONSE"

# Сохранение токена из ответа
if [ "$(echo "$RESPONSE" | jq -r '.success')" == "true" ]; then
    TOKEN=$(echo "$RESPONSE" | jq -r '.data.access_token')
    echo -e "${GREEN}Токен получен и сохранен${NC}"
fi

# Тест 2: Авторизация пользователя
echo -e "${BLUE}Тест 2: Авторизация пользователя${NC}"
RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{
        "email": "test@example.com",
        "password": "password123",
        "device_token": "test-device-token"
    }')
STATUS=$?
HTTP_CODE=$(echo "$RESPONSE" | jq -r '.success')
display_response $STATUS $HTTP_CODE "$RESPONSE"

# Обновление токена из ответа авторизации
if [ "$(echo "$RESPONSE" | jq -r '.success')" == "true" ]; then
    TOKEN=$(echo "$RESPONSE" | jq -r '.data.access_token')
    echo -e "${GREEN}Токен обновлен${NC}"
fi

# Тест 3: Получение профиля пользователя
echo -e "${BLUE}Тест 3: Получение профиля пользователя${NC}"
RESPONSE=$(curl -s -X GET "$API_URL/user/profile" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json")
STATUS=$?
HTTP_CODE=$(echo "$RESPONSE" | jq -r '.success')
display_response $STATUS $HTTP_CODE "$RESPONSE"

# Тест 4: Сохранение цели фитнеса
echo -e "${BLUE}Тест 4: Сохранение цели фитнеса${NC}"
RESPONSE=$(curl -s -X POST "$API_URL/user/fitness-goal" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
        "goal": "weight-loss"
    }')
STATUS=$?
HTTP_CODE=$(echo "$RESPONSE" | jq -r '.success')
display_response $STATUS $HTTP_CODE "$RESPONSE"

# Тест 5: Получение цели фитнеса
echo -e "${BLUE}Тест 5: Получение цели фитнеса${NC}"
RESPONSE=$(curl -s -X GET "$API_URL/user/fitness-goal" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json")
STATUS=$?
HTTP_CODE=$(echo "$RESPONSE" | jq -r '.success')
display_response $STATUS $HTTP_CODE "$RESPONSE"

# Тест 6: Выход из системы
echo -e "${BLUE}Тест 6: Выход из системы${NC}"
RESPONSE=$(curl -s -X POST "$API_URL/auth/logout" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json")
STATUS=$?
HTTP_CODE=$(echo "$RESPONSE" | jq -r '.success')
display_response $STATUS $HTTP_CODE "$RESPONSE"

echo -e "${BLUE}Тесты API завершены${NC}" 