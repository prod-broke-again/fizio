# WebSocket Сервер для Fizio

WebSocket сервер для обеспечения функционала чата и уведомлений в реальном времени для приложения Fizio.

## Установка

```bash
# Перейти в директорию WebSocket сервера
cd ws_server

# Установить зависимости
npm install
```

## Запуск сервера

### Запуск в режиме разработки (с автоперезагрузкой)

```bash
npm run dev
```

### Запуск в продакшн режиме

```bash
npm start
```

## Конфигурация

Сервер использует следующие переменные окружения, которые читаются из файла `.env` в корне проекта Laravel:

- `REDIS_HOST` - хост Redis (по умолчанию: localhost)
- `REDIS_PORT` - порт Redis (по умолчанию: 6379)
- `REDIS_PASSWORD` - пароль для Redis (по умолчанию: null)
- `WS_PORT` - порт для WebSocket сервера (по умолчанию: 3000)
- `APP_URL` - URL Laravel API (по умолчанию: http://localhost)

## Запуск через PM2

Для запуска в режиме демона рекомендуется использовать PM2:

```bash
# Установка PM2 (если еще не установлен)
npm install -g pm2

# Запуск сервера через PM2
pm2 start server.js --name "fizio-ws"

# Проверка статуса
pm2 status

# Просмотр логов
pm2 logs fizio-ws

# Автозапуск при перезагрузке системы
pm2 startup
pm2 save
```

## API для клиентов

### События для клиентов

- `chat_response` - получение нового сообщения от чат-ассистента
- `authenticated` - успешная аутентификация
- `authentication_error` - ошибка аутентификации

### События от клиентов

- `authenticate` - отправка токена для аутентификации
- `ping` - проверка активности соединения

## Пример использования на клиенте (Ionic Vue)

### Сервис WebSocket соединения (src/services/chatSocket.ts)

```typescript
// src/services/chatSocket.ts
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { io, Socket } from 'socket.io-client';
import { useStorage } from '@vueuse/core';

export function useChatSocket() {
  const socket = ref<Socket | null>(null);
  const connected = ref(false);
  const messages = ref<any[]>([]);
  const token = useStorage('auth_token', '');

  const initializeSocket = () => {
    socket.value = io(import.meta.env.VITE_WS_URL || 'http://localhost:3001');
    
    socket.value.on('connect', () => {
      console.log('Соединение установлено');
      authenticate();
    });
    
    socket.value.on('disconnect', () => {
      console.log('Соединение потеряно');
      connected.value = false;
    });
    
    socket.value.on('authenticated', () => {
      console.log('Аутентификация успешна');
      connected.value = true;
    });
    
    socket.value.on('authentication_error', (error) => {
      console.error('Ошибка аутентификации:', error);
      connected.value = false;
    });
    
    socket.value.on('chat_response', (data) => {
      console.log('Получено сообщение:', data);
      
      // Если сообщение уже есть, обновляем его
      const index = messages.value.findIndex(m => m.id === data.id);
      if (index !== -1) {
        messages.value[index] = data;
      } else {
        messages.value.unshift(data);
      }
    });
  };

  const authenticate = () => {
    if (socket.value && token.value) {
      socket.value.emit('authenticate', token.value);
    }
  };

  // Проверка активности соединения
  const ping = (): Promise<any> => {
    return new Promise((resolve) => {
      if (socket.value) {
        socket.value.emit('ping', (response) => {
          resolve(response);
        });
      } else {
        resolve({ error: 'Нет подключения' });
      }
    });
  };

  // Загрузка истории сообщений с сервера
  const loadHistory = async () => {
    try {
      const response = await fetch(`${import.meta.env.VITE_API_URL}/api/chat/history`, {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json'
        }
      });
      
      const data = await response.json();
      if (data.success) {
        messages.value = data.data;
      }
    } catch (error) {
      console.error('Ошибка загрузки истории:', error);
    }
  };

  onMounted(() => {
    initializeSocket();
    loadHistory();
  });

  onUnmounted(() => {
    if (socket.value) {
      socket.value.disconnect();
    }
  });

  return {
    connected,
    messages,
    ping,
    loadHistory
  };
}
```

### Компонент чата (src/views/ChatView.vue)

```vue
<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Фитнес-ассистент</ion-title>
        <ion-buttons slot="end">
          <ion-button v-if="connected" color="success">
            <ion-icon :icon="checkmarkCircle"></ion-icon>
          </ion-button>
          <ion-button v-else color="danger">
            <ion-icon :icon="alertCircle"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>
    
    <ion-content>
      <div class="chat-container">
        <ion-list lines="none">
          <ion-item v-for="message in messages" :key="message.id" class="chat-message">
            <div class="message-container">
              <div class="user-message">
                <ion-text color="primary">
                  <p>{{ message.message }}</p>
                </ion-text>
                <small>{{ formatDate(message.created_at) }}</small>
              </div>
              
              <div v-if="message.is_processing" class="assistant-message processing">
                <ion-spinner name="dots"></ion-spinner>
              </div>
              
              <div v-else-if="message.response" class="assistant-message">
                <ion-text color="dark">
                  <p v-html="formatMessage(message.response)"></p>
                </ion-text>
              </div>
            </div>
          </ion-item>
        </ion-list>
      </div>
    </ion-content>
    
    <ion-footer>
      <ion-toolbar>
        <div class="input-container">
          <ion-textarea
            v-model="newMessage"
            placeholder="Задайте вопрос..."
            auto-grow
            rows="1"
            max-rows="4"
            class="chat-input"
            @keydown.enter.prevent="sendMessage"
          ></ion-textarea>
          
          <ion-button @click="sendMessage" :disabled="!newMessage.trim()">
            <ion-icon :icon="send"></ion-icon>
          </ion-button>
          
          <ion-button @click="startVoiceRecording" color="secondary">
            <ion-icon :icon="micOutline"></ion-icon>
          </ion-button>
        </div>
      </ion-toolbar>
    </ion-footer>
  </ion-page>
</template>

<script setup>
import { ref, computed } from 'vue';
import { 
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonFooter,
  IonTextarea, IonButton, IonIcon, IonButtons, IonList, IonItem,
  IonText, IonSpinner
} from '@ionic/vue';
import { 
  sendOutline as send,
  micOutline, 
  checkmarkCircle, 
  alertCircle 
} from 'ionicons/icons';
import { useChatSocket } from '@/services/chatSocket';

const { connected, messages } = useChatSocket();
const newMessage = ref('');
const isRecording = ref(false);

// Форматирование даты
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('ru-RU', {
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
};

// Форматирование сообщения (конвертация \n в <br>)
const formatMessage = (text) => {
  return text.replace(/\n/g, '<br>');
};

// Отправка текстового сообщения
const sendMessage = async () => {
  if (!newMessage.value.trim()) return;
  
  try {
    await fetch(`${import.meta.env.VITE_API_URL}/api/chat/send`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ message: newMessage.value })
    });
    
    newMessage.value = '';
  } catch (error) {
    console.error('Ошибка отправки:', error);
  }
};

// Запись голосового сообщения (заглушка, требуется реализация)
const startVoiceRecording = () => {
  isRecording.value = true;
  // Здесь код для записи и отправки голосового сообщения
  // ...
};
</script>

<style scoped>
.chat-container {
  padding: 10px;
  display: flex;
  flex-direction: column;
}

.message-container {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.user-message {
  align-self: flex-end;
  background-color: #f0f8ff;
  border-radius: 15px 15px 0 15px;
  padding: 10px 15px;
  margin-bottom: 5px;
  max-width: 80%;
}

.assistant-message {
  align-self: flex-start;
  background-color: #f5f5f5;
  border-radius: 15px 15px 15px 0;
  padding: 10px 15px;
  margin-bottom: 10px;
  max-width: 80%;
}

.assistant-message.processing {
  padding: 15px;
}

.chat-message {
  --padding-start: 0;
  --padding-end: 0;
  margin-bottom: 10px;
}

.input-container {
  display: flex;
  align-items: center;
  padding: 5px 10px;
}

.chat-input {
  margin-right: 10px;
  border-radius: 20px;
  --padding-start: 15px;
  --padding-end: 15px;
  --padding-top: 10px;
  --padding-bottom: 10px;
  --background: #f5f5f5;
}
</style>

### Конфигурация проекта

Добавьте в файл `.env` или `src/environments/environment.ts`:

```
VITE_WS_URL=http://localhost:3001
VITE_API_URL=https://fizio.online
```

### Установка зависимостей

```bash
npm install socket.io-client@4.7.2
npm install @vueuse/core
``` 