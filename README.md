# Fizio - Фитнес-приложение с AI-ассистентом 🐼

![Fizio App](https://fizio.online/img/logo.png)

## О проекте

**Fizio** - это современное фитнес-приложение, которое помогает пользователям следить за тренировками, питанием и достигать своих целей в области здорового образа жизни. Особенность приложения - дружелюбный AI-ассистент в образе панды, который дает персонализированные рекомендации и отвечает на вопросы в реальном времени.

## Ключевые возможности

### Для пользователей
- 🤖 **AI-ассистент в образе панды** - задавайте вопросы и получайте экспертные советы по фитнесу
- 📊 **Отслеживание прогресса** - мониторинг тренировок и достижений
- 📅 **Планирование тренировок** - создание и отслеживание расписания занятий
- 🔄 **История общения** - ассистент "помнит" предыдущие разговоры и дает контекстные ответы
- 📱 **Интеграция с Telegram** - доступ к функционалу через Telegram-бота

### Технически
- ⚡ **Общение в реальном времени** - WebSocket соединение для мгновенных ответов
- 🧠 **GPT 4.1 Nano модель** - современный AI через GPTunnel API
- 📝 **Сохранение контекста** - история из 20 последних сообщений для более точных ответов
- 🔒 **Безопасная аутентификация** - система токенов и защита API

## Технический стек

### Backend
- **Laravel 10** - основной фреймворк
- **PHP 8.2** - язык программирования
- **Node.js** - для WebSocket сервера
- **Redis** - для передачи данных между сервисами

### Frontend
- **Vue.js** - фреймворк для интерфейса
- **Tailwind CSS** - стилизация
- **Socket.io** - клиентская библиотека для WebSocket

### Инфраструктура
- **MySQL** - основная база данных
- **Laravel Queue** - система очередей для обработки сообщений
- **GPTunnel API** - провайдер AI-моделей

## Установка

### Предварительные требования
- PHP 8.2+
- Composer
- Node.js 16+
- MySQL 8+
- Redis

### Шаги установки

1. **Клонирование репозитория**
   ```bash
   git clone https://github.com/yourusername/fizio.git
   cd fizio
   ```

2. **Установка PHP зависимостей**
   ```bash
   composer install
   ```

3. **Установка JavaScript зависимостей**
   ```bash
   npm install
   ```

4. **Настройка окружения**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Настройка базы данных**
   Отредактируйте файл `.env`, указав параметры подключения к базе данных:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fizio
   DB_USERNAME=root
   DB_PASSWORD=password
   ```

6. **Настройка Redis**
   В файле `.env` укажите параметры подключения к Redis:
   ```
   REDIS_CLIENT=predis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   REDIS_DB=0
   ```

7. **Настройка GPTunnel API**
   Добавьте в `.env` свой API ключ:
   ```
   GPTUNNEL_API_KEY=your-api-key
   GPTUNNEL_ENABLED=true
   GPTUNNEL_MODEL=gpt-4.1-nano
   ```

8. **Применение миграций**
   ```bash
   php artisan migrate
   ```

9. **Компиляция ассетов**
   ```bash
   npm run dev
   ```

10. **Запуск приложения**
    ```bash
    php artisan serve
    ```

11. **Запуск WebSocket сервера**
    ```bash
    cd ws_server
    node server.js
    ```

## Структура проекта

```
fizio/
├── app/                 # PHP код приложения
│   ├── Http/            # Контроллеры и middleware
│   ├── Jobs/            # Задачи очередей
│   └── Models/          # Модели данных
├── config/              # Конфигурационные файлы
├── database/            # Миграции и сиды
├── public/              # Публично доступные файлы
├── resources/           # Исходные ресурсы (JS, CSS, Vue)
├── routes/              # Определения маршрутов
├── ws_server/           # Сервер WebSocket на Node.js
└── .env                 # Файл с переменными окружения
```

## API Endpoints

### Аутентификация
- `POST /api/auth/register` - Регистрация пользователя
- `POST /api/auth/login` - Вход в систему
- `POST /api/auth/logout` - Выход из системы

### Пользователь
- `GET /api/user/profile` - Получение профиля пользователя
- `POST /api/user/profile` - Обновление профиля
- `POST /api/user/fitness-goal` - Сохранение цели фитнеса
- `GET /api/user/fitness-goal` - Получение текущей цели

### Чат с AI
- `POST /api/chat/send` - Отправка сообщения ассистенту
- `GET /api/chat/history` - Получение истории сообщений
- `POST /api/chat/voice` - Отправка голосового сообщения (в разработке)
- `DELETE /api/chat/clear` - Очистка истории сообщений

### Тренировки
- `GET /api/workouts/schedule` - Получение расписания тренировок
- `POST /api/workouts` - Создание новой тренировки
- `PUT /api/workouts/{id}` - Обновление тренировки
- `DELETE /api/workouts/{id}` - Удаление тренировки

## WebSocket API

Для подключения к WebSocket серверу используйте:
```javascript
const socket = io('ws://your-domain.com:3001');

// Аутентификация
socket.emit('authenticate', 'your-auth-token');

// Получение ответов от ассистента
socket.on('chat_response', (data) => {
  console.log(data);
});
```

## Контрибьюторы

- Eugeny - Разработчик
- Команда Fizio

## Лицензия

MIT License

---

Сделано с ❤️ командой Fizio
