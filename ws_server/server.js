const server = require('http').createServer();
const io = require('socket.io')(server, {
  cors: {
    origin: "*", // В продакшне замените на конкретный домен вашего фронтенда
    methods: ["GET", "POST"],
    credentials: true
  }
});
const Redis = require('ioredis');
const dotenv = require('dotenv');
const axios = require('axios');

console.log('Запуск WebSocket сервера...');

// Загружаем переменные окружения
dotenv.config({ path: '../.env' });
console.log('Загружены переменные окружения из ../env');

// Настройки Redis из переменных окружения или дефолтные значения
const redisConfig = {
  host: process.env.REDIS_HOST || 'localhost',
  port: process.env.REDIS_PORT || 6379,
  password: process.env.REDIS_PASSWORD || null,
  db: 0, // Явно указываем БД 0 для совместимости с Laravel
};
console.log(`Конфигурация Redis: хост=${redisConfig.host}, порт=${redisConfig.port}, БД=${redisConfig.db}, пароль=${redisConfig.password ? 'установлен' : 'не установлен'}`);

// Порт для WebSocket сервера
const WS_PORT = process.env.WS_PORT || 3001;
console.log(`Порт WebSocket сервера: ${WS_PORT}`);

// URL API для проверки токена
const API_URL = process.env.APP_URL || 'https://fizio.online';
console.log(`URL API для проверки токена: ${API_URL}`);

// Инициализация Redis клиентов
console.log('Инициализация Redis клиентов...');
const redisSubscriber = new Redis(redisConfig);
const redisPubClient = new Redis(redisConfig);

// Каналы Redis
const CHAT_CHANNEL = 'chat:messages';
console.log(`Основной канал чата: ${CHAT_CHANNEL}`);
const STATUS_CHANNEL = 'chat:status';

// Мапа активных соединений: userId -> [socket1, socket2, ...]
const userSockets = new Map();

// Функция для логирования статистики соединений
function logConnectionStats() {
  const totalUsers = userSockets.size;
  let totalConnections = 0;
  userSockets.forEach(sockets => {
    totalConnections += sockets.length;
  });
  console.log(`[СТАТИСТИКА] Активные пользователи: ${totalUsers}, всего соединений: ${totalConnections}`);
  console.log(`[СТАТИСТИКА] ID пользователей: ${Array.from(userSockets.keys()).join(', ') || 'нет активных пользователей'}`);
}

// Периодический вывод статистики
setInterval(logConnectionStats, 60000); // Каждую минуту

// Логирование статуса Redis соединения
redisSubscriber.on('connect', () => {
  console.log('[REDIS] Подписчик подключен успешно');
});

redisPubClient.on('connect', () => {
  console.log('[REDIS] Издатель подключен успешно');
});

redisSubscriber.on('error', (err) => {
  console.error('[REDIS] Ошибка соединения подписчика:', err);
});

redisPubClient.on('error', (err) => {
  console.error('[REDIS] Ошибка соединения издателя:', err);
});

// Подписываемся на канал сообщений в Redis
console.log(`[REDIS] Подписываемся на канал: ${CHAT_CHANNEL}`);
redisSubscriber.subscribe(CHAT_CHANNEL, (err, count) => {
  if (err) {
    console.error('[REDIS] Ошибка при подписке на канал:', err);
  } else {
    console.log(`[REDIS] Успешно подписаны на ${count} канал(ов)`);
  }
});

// Дополнительная подписка на канал статуса
redisSubscriber.subscribe(STATUS_CHANNEL, (err, count) => {
  if (err) {
    console.error('[REDIS] Ошибка при подписке на канал статуса:', err);
  } else {
    console.log(`[REDIS] Успешно подписаны на ${STATUS_CHANNEL}`);
  }
});

// Получаем сообщения из Redis и отправляем клиентам
redisSubscriber.on('message', (channel, message) => {
  console.log(`[REDIS] Получено сообщение из канала: ${channel}, длина: ${message.length} байт`);
  
  if (channel === CHAT_CHANNEL) {
    try {
      const data = JSON.parse(message);
      
      // Проверка наличия обязательных полей
      if (!data.user_id) {
        console.warn(`[REDIS-CHAT] Сообщение без user_id:`, message);
        return;
      }
      
      const userId = data.user_id.toString();
      const messageId = data.id || 'unknown';
      const isProcessing = data.is_processing || false;
      
      console.log(`[REDIS-CHAT] Сообщение ${messageId} для пользователя ${userId} (обработка: ${isProcessing ? 'в процессе' : 'завершена'})`);
      
      // Отправляем сообщение всем сокетам пользователя
      const sockets = userSockets.get(userId);
      if (sockets && sockets.length > 0) {
        console.log(`[SOCKET] Найдено ${sockets.length} активных соединений для пользователя ${userId}`);
        sockets.forEach((socket, index) => {
          socket.emit('chat_response', data);
        });
        console.log(`[SOCKET] Сообщение ${messageId} отправлено ${sockets.length} соединениям пользователя ${userId}`);
      } else {
        console.log(`[SOCKET] Пользователь ${userId} не имеет активных соединений`);
      }
    } catch (error) {
      console.error('[REDIS-CHAT] Ошибка при обработке сообщения:', error);
      console.error('[REDIS-CHAT] Исходное сообщение:', message);
    }
  } else if (channel === STATUS_CHANNEL) {
    try {
      const statusData = JSON.parse(message);
      
      // Проверка наличия обязательных полей
      if (!statusData.user_id || !statusData.status) {
        console.warn(`[STATUS] Некорректный формат сообщения:`, message);
        return;
      }
      
      console.log(`[STATUS] Получено обновление статуса пользователя ${statusData.user_id}: ${statusData.status}`);
    } catch (error) {
      console.error('[STATUS] Ошибка при обработке статуса:', error);
    }
  }
});

// Аутентификация пользователя при подключении
io.on('connection', (socket) => {
  const socketId = socket.id;
  const clientIp = socket.handshake.address;
  console.log(`[SOCKET] Новое соединение: ${socketId} с IP ${clientIp}`);
  
  let authenticated = false;
  let userId = null;

  // Таймаут для неаутентифицированных соединений
  const authTimeout = setTimeout(() => {
    if (!authenticated) {
      console.log(`[SOCKET] Таймаут аутентификации для соединения ${socketId}`);
      socket.emit('authentication_timeout', { message: 'Время аутентификации истекло' });
      socket.disconnect(true);
    }
  }, 30000); // 30 секунд на аутентификацию

  socket.on('authenticate', async (token) => {
    console.log(`[AUTH] Попытка аутентификации для соединения ${socketId}`);
    
    if (!token) {
      console.log(`[AUTH] Ошибка: токен не предоставлен для ${socketId}`);
      socket.emit('authentication_error', { message: 'Токен не предоставлен' });
      return;
    }
    
    try {
      console.log(`[AUTH] Проверка токена через API: ${API_URL}/api/user/profile`);
      // Проверка токена через API запрос к Laravel
      const response = await axios.get(`${API_URL}/api/user/profile`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      
      console.log(`[AUTH] Получен ответ от API, статус: ${response.status}`);
      
      if (response.data && response.data.success && response.data.data && response.data.data.user && response.data.data.user.id) {
        userId = response.data.data.user.id.toString();
        authenticated = true;
        
        // Отменяем таймаут аутентификации
        clearTimeout(authTimeout);
        
        // Добавляем сокет в список соединений пользователя
        if (!userSockets.has(userId)) {
          userSockets.set(userId, []);
          console.log(`[AUTH] Создана новая запись для пользователя ${userId}`);
        }
        userSockets.get(userId).push(socket);
        const connectionCount = userSockets.get(userId).length;
        
        console.log(`[AUTH] Пользователь ${userId} аутентифицирован, сокет ${socketId} добавлен (всего соединений: ${connectionCount})`);
        socket.emit('authenticated', { 
          status: 'success',
          user: response.data.data.user 
        });
        
        // Отправка уведомления в чат, что пользователь подключен
        console.log(`[STATUS] Публикация статуса 'online' для пользователя ${userId}`);
        redisPubClient.publish('chat:status', JSON.stringify({
          user_id: userId,
          status: 'online',
          timestamp: new Date().toISOString()
        }));
        
        // Вывод обновленной статистики после авторизации
        logConnectionStats();
      } else {
        console.error(`[AUTH] Неверная структура ответа для ${socketId}:`, response.data);
        socket.emit('authentication_error', { message: 'Невалидный токен или неверная структура ответа' });
      }
    } catch (error) {
      console.error(`[AUTH] Ошибка аутентификации для ${socketId}:`, error.message);
      if (error.response) {
        console.error(`[AUTH] Ответ сервера:`, error.response.status, error.response.data);
      }
      socket.emit('authentication_error', { message: 'Ошибка аутентификации' });
    }
  });

  // Обработка отключения клиента
  socket.on('disconnect', () => {
    if (authenticated && userId) {
      // Удаляем сокет из списка соединений пользователя
      const sockets = userSockets.get(userId);
      if (sockets) {
        const index = sockets.indexOf(socket);
        if (index !== -1) {
          sockets.splice(index, 1);
          console.log(`[SOCKET] Соединение ${socketId} удалено для пользователя ${userId} (осталось соединений: ${sockets.length})`);
        }

        // Если у пользователя больше нет активных соединений, удаляем запись
        if (sockets.length === 0) {
          userSockets.delete(userId);
          console.log(`[SOCKET] Пользователь ${userId} больше не имеет активных соединений, запись удалена`);

          // Отправка уведомления в чат, что пользователь отключен
          console.log(`[STATUS] Публикация статуса 'offline' для пользователя ${userId}`);
          redisPubClient.publish('chat:status', JSON.stringify({
            user_id: userId,
            status: 'offline',
            timestamp: new Date().toISOString()
          }));
        }
      }
      console.log(`[SOCKET] Клиент отключен. Пользователь: ${userId}, соединение: ${socketId}`);
    } else {
      console.log(`[SOCKET] Неаутентифицированный клиент отключен, соединение: ${socketId}`);
    }
    
    // Очистка таймаута, если он еще активен
    clearTimeout(authTimeout);
    
    // Вывод обновленной статистики после отключения
    logConnectionStats();
  });

  // Пинг для поддержания соединения
  socket.on('ping', (callback) => {
    if (typeof callback === 'function') {
      callback({ time: new Date().toISOString() });
    }
  });
  
  // Дополнительный обработчик для отладки
  socket.on('debug', (data) => {
    console.log(`[DEBUG] Получен запрос отладки от ${socketId}:`, data);
    socket.emit('debug_response', { 
      received: data,
      server_time: new Date().toISOString(),
      is_authenticated: authenticated,
      user_id: userId
    });
  });
});

// Запуск сервера
server.listen(WS_PORT, () => {
  console.log(`[SERVER] WebSocket сервер запущен на порту ${WS_PORT}`);
  console.log(`[SERVER] Подключен к Redis: ${redisConfig.host}:${redisConfig.port}`);
  console.log(`[SERVER] Время запуска: ${new Date().toISOString()}`);
});

// Обработка необработанных исключений
process.on('uncaughtException', (err) => {
  console.error('[ERROR] Необработанное исключение:', err);
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('[ERROR] Необработанное отклонение промиса:', reason);
});
