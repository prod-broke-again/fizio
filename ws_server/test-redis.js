const Redis = require('ioredis');
const dotenv = require('dotenv');

// Загрузка переменных окружения
dotenv.config({ path: '../.env' });
console.log('Загружены переменные окружения из ../env');

// Настройки Redis
const redisConfig = {
  host: process.env.REDIS_HOST || 'localhost',
  port: process.env.REDIS_PORT || 6379,
  password: process.env.REDIS_PASSWORD || null
};

console.log(`Конфигурация Redis: хост=${redisConfig.host}, порт=${redisConfig.port}, пароль=${redisConfig.password ? 'установлен' : 'не установлен'}`);

// Создаем клиент Redis
const redis = new Redis(redisConfig);

// Тестовое сообщение
const testMessage = {
  id: 9999,
  user_id: 2,
  message: "Тестовое сообщение от Node.js",
  response: "Это тестовый ответ от Node.js Redis client",
  created_at: new Date().toISOString(),
  is_processing: false
};

// Отправляем сообщение
redis.publish('chat:messages', JSON.stringify(testMessage))
  .then(result => {
    console.log(`Сообщение успешно отправлено в Redis. Количество получателей: ${result}`);
    console.log(`Отправленное сообщение:`, JSON.stringify(testMessage, null, 2));
    
    // Подождем немного перед завершением
    setTimeout(() => {
      redis.disconnect();
      console.log('Соединение с Redis закрыто');
      process.exit(0);
    }, 1000);
  })
  .catch(err => {
    console.error('Ошибка при отправке сообщения в Redis:', err);
    redis.disconnect();
    process.exit(1);
  }); 