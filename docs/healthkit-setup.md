# Настройка HealthKit

## Установка

1. Установите пакет:
```bash
npm i --save @perfood/capacitor-healthkit
```

2. Обновите Capacitor:
```bash
npx cap update
```

3. Добавьте в `info.plist` следующие разрешения:
```xml
<key>NSHealthShareUsageDescription</key>
<string>Приложению требуется доступ к данным о здоровье для отслеживания вашей активности</string>
<key>NSHealthUpdateUsageDescription</key>
<string>Приложению требуется доступ к данным о здоровье для обновления вашей активности</string>
```

4. Включите HealthKit в capabilities вашего Xcode проекта:
   - Откройте проект в Xcode
   - Выберите ваш target
   - Перейдите во вкладку "Signing & Capabilities"
   - Нажмите "+" и добавьте "HealthKit"

## Использование в коде

```typescript
import {
  ActivityData,
  CapacitorHealthkit,
  OtherData,
  QueryOutput,
  SampleNames,
  SleepData,
} from '@perfood/capacitor-healthkit';

const READ_PERMISSIONS = [
  'calories',
  'stairs',
  'activity',
  'steps',
  'distance',
  'duration',
  'weight'
];

// Запрос разрешений
async function requestHealthKitAuthorization() {
  try {
    await CapacitorHealthkit.requestAuthorization({
      all: [''],
      read: READ_PERMISSIONS,
      write: [''],
    });
  } catch (error) {
    console.error('Ошибка получения разрешений HealthKit:', error);
  }
}

// Получение данных о шагах
async function getStepsData(startDate: Date, endDate: Date = new Date()) {
  try {
    const queryOptions = {
      sampleName: SampleNames.STEPS,
      startDate: startDate.toISOString(),
      endDate: endDate.toISOString(),
      limit: 0,
    };

    return await CapacitorHealthkit.queryHKitSampleType<ActivityData>(queryOptions);
  } catch (error) {
    console.error('Ошибка получения данных о шагах:', error);
    return undefined;
  }
}

// Получение данных о калориях
async function getCaloriesData(startDate: Date, endDate: Date = new Date()) {
  try {
    const queryOptions = {
      sampleName: SampleNames.CALORIES,
      startDate: startDate.toISOString(),
      endDate: endDate.toISOString(),
      limit: 0,
    };

    return await CapacitorHealthkit.queryHKitSampleType<ActivityData>(queryOptions);
  } catch (error) {
    console.error('Ошибка получения данных о калориях:', error);
    return undefined;
  }
}
```

## Доступные типы данных

- `calories` - калории
- `stairs` - подъемы по лестнице
- `activity` - активность
- `steps` - шаги
- `distance` - расстояние
- `duration` - продолжительность
- `weight` - вес

## Синхронизация с бэкендом

Для синхронизации данных с HealthKit и вашим бэкендом, используйте следующий эндпоинт:

```http
POST /api/healthkit/sync
```

Тело запроса:
```json
{
    "steps": 8500,
    "calories": 1200,
    "distance": 5.2,
    "duration": 45,
    "timestamp": "2024-02-13T12:00:00Z"
}
```

## Важные замечания

1. HealthKit доступен только на iOS устройствах
2. Для тестирования требуется реальное устройство (не работает в симуляторе)
3. Приложение должно быть подписано действительным сертификатом разработчика
4. Все приложения, использующие HealthKit, должны быть проверены Apple перед публикацией в App Store 