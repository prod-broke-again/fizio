# Интеграция с Telegram Bot API

Это руководство описывает настройку и использование Telegram бота для фитнес-приложения Fizio.

## Настройка Telegram бота

### 1. Создание бота через BotFather

1. Откройте Telegram и найдите @BotFather
2. Отправьте команду `/newbot`
3. Укажите имя бота (например, "Fizio Fitness Bot")
4. Придумайте уникальное имя пользователя, которое должно заканчиваться на "bot" (например, "fizio_fitness_bot")
5. BotFather выдаст вам токен бота. Сохраните его в безопасном месте!

### 2. Настройка WebApp через BotFather

1. Отправьте `/mybots` в BotFather
2. Выберите вашего бота
3. Выберите "Bot Settings" -> "Menu Button"
4. Выберите "Configure menu button"
5. Введите текст кнопки (например, "Открыть Fizio")
6. Введите URL вашего WebApp: `https://fizio.online/telegram/webapp`

### 3. Настройка окружения в Laravel

Добавьте следующие переменные в файл `.env`:

```
TELEGRAM_BOT_NAME=FizioBot
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_WEBHOOK_URL=https://fizio.online/api/telegram/webhook
TELEGRAM_WEBAPP_URL=https://fizio.online/telegram/webapp
```

### 4. Настройка Webhook для бота

Выполните запрос для настройки webhook:

```
GET https://fizio.online/api/telegram/setup-webhook
```

Это настроит бота на получение уведомлений о сообщениях через webhook.

## Структура интеграции

### Файлы и компоненты

1. **Конфигурация**:
   - `config/telegram.php` - основные настройки бота

2. **Сервисы**:
   - `app/Services/TelegramService.php` - сервис для работы с Telegram API

3. **Контроллеры**:
   - `app/Http/Controllers/TelegramController.php` - обработка webhook и команд бота
   - `app/Http/Controllers/API/TelegramAuthController.php` - авторизация через Telegram

4. **Представления**:
   - `resources/views/telegram/webapp.blade.php` - шаблон для загрузки Ionic приложения в Telegram WebApp

5. **Миграции**:
   - `database/migrations/2025_04_25_000000_add_telegram_fields_to_users_table.php` - добавление полей Telegram в таблицу пользователей

### Маршруты API

- `POST /api/auth/telegram` - авторизация через Telegram WebApp
- `POST /api/auth/telegram/link` - связывание существующего аккаунта с Telegram
- `POST /api/telegram/webhook` - webhook для получения сообщений от Telegram
- `GET /api/telegram/setup-webhook` - настройка webhook URL
- `GET /api/telegram/webhook-info` - информация о настройках webhook

### Маршруты Web

- `GET /telegram/webapp` - страница для загрузки Ionic приложения внутри Telegram

## Команды бота

Реализованы следующие команды:

- `/start` - Начало работы с ботом, показывает приветственное сообщение и клавиатуру
- `/webapp` или `/app` - Открывает WebApp в Telegram
- `/profile` - Показывает информацию о профиле пользователя
- `/stats` - Показывает статистику тренировок
- `/workout` - Показывает информацию о следующей тренировке
- `/help` - Показывает справочную информацию о боте

## Интеграция с Ionic приложением

### Автоматическая регистрация через Telegram

Чтобы пользователям не нужно было видеть стандартные экраны входа и регистрации при открытии из Telegram, выполните следующие шаги:

#### 1. Создайте сервис Telegram в вашем Ionic приложении

```typescript
// src/app/services/telegram.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { Router } from '@angular/router';
import { AuthService } from './auth.service';
import { environment } from '../../environments/environment';

declare global {
  interface Window {
    Telegram: any;
  }
}

@Injectable({
  providedIn: 'root'
})
export class TelegramService {
  private tg: any;
  private isTelegramWebApp: boolean = false;

  constructor(
    private http: HttpClient, 
    private authService: AuthService,
    private router: Router
  ) {
    // Проверяем, запущено ли приложение в Telegram WebApp
    this.isTelegramWebApp = window.Telegram && window.Telegram.WebApp;
    
    if (this.isTelegramWebApp) {
      this.tg = window.Telegram.WebApp;
      this.tg.ready();
      
      // Применяем стили Telegram
      this.applyTelegramTheme();
    }
  }

  // Проверка, открыто ли приложение в Telegram WebApp
  public isInTelegramWebApp(): boolean {
    return this.isTelegramWebApp;
  }

  // Автоматический вход через Telegram
  public autoLogin(): Observable<boolean> {
    if (!this.isInTelegramWebApp() || !this.tg.initDataUnsafe.user) {
      return of(false);
    }

    const userData = this.tg.initDataUnsafe;
    const user = userData.user;

    // Формируем данные для запроса к API
    const authData = {
      id: user.id,
      first_name: user.first_name,
      last_name: user.last_name || '',
      username: user.username || '',
      auth_date: userData.auth_date,
      hash: userData.hash
    };

    return this.http.post<any>(`${environment.apiUrl}/auth/telegram`, authData)
      .pipe(
        tap(response => {
          if (response.success) {
            // Сохраняем токен и данные пользователя
            this.authService.setToken(response.data.access_token);
            this.authService.setUser(response.data.user);
          }
        }),
        map(response => response.success),
        catchError(error => {
          console.error('Ошибка авторизации через Telegram:', error);
          return of(false);
        })
      );
  }

  // Применение темы Telegram к приложению
  private applyTelegramTheme(): void {
    if (!this.isInTelegramWebApp()) return;

    document.documentElement.style.setProperty('--ion-background-color', this.tg.themeParams.bg_color);
    document.documentElement.style.setProperty('--ion-text-color', this.tg.themeParams.text_color);
    document.documentElement.style.setProperty('--ion-color-primary', this.tg.themeParams.button_color || '#3880ff');
    
    // Настройка главной кнопки Telegram
    this.tg.MainButton.setParams({
      text: 'Начать тренировку',
      color: this.tg.themeParams.button_color || '#3880ff',
      text_color: '#ffffff'
    });
  }

  // Показать главную кнопку
  public showMainButton(text?: string): void {
    if (!this.isInTelegramWebApp()) return;
    
    if (text) {
      this.tg.MainButton.setText(text);
    }
    this.tg.MainButton.show();
  }

  // Скрыть главную кнопку
  public hideMainButton(): void {
    if (!this.isInTelegramWebApp()) return;
    
    this.tg.MainButton.hide();
  }

  // Установить обработчик нажатия главной кнопки
  public onMainButtonClick(callback: () => void): void {
    if (!this.isInTelegramWebApp()) return;
    
    this.tg.MainButton.onClick(callback);
  }

  // Закрыть WebApp
  public close(): void {
    if (!this.isInTelegramWebApp()) return;
    
    this.tg.close();
  }
}
```

#### 2. Добавьте Guard для проверки авторизации в Telegram

```typescript
// src/app/guards/telegram-auth.guard.ts
import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { Observable, of } from 'rxjs';
import { switchMap, tap } from 'rxjs/operators';
import { TelegramService } from '../services/telegram.service';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class TelegramAuthGuard implements CanActivate {
  constructor(
    private telegramService: TelegramService,
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(): Observable<boolean> {
    // Проверка, открыто ли приложение в Telegram WebApp
    if (this.telegramService.isInTelegramWebApp()) {
      // Если пользователь уже авторизован, пропускаем
      if (this.authService.isAuthenticated()) {
        return of(true);
      }

      // Пытаемся авторизоваться через Telegram
      return this.telegramService.autoLogin().pipe(
        tap(success => {
          if (!success) {
            // Если авторизация через Telegram не удалась, но мы в Telegram WebApp,
            // показываем сообщение пользователю и закрываем приложение
            console.error('Не удалось авторизоваться через Telegram');
            // Можно добавить показ уведомления или другой обработки ошибки
          }
        })
      );
    } else {
      // Если приложение открыто не в Telegram, проверяем обычную авторизацию
      const isAuth = this.authService.isAuthenticated();
      if (!isAuth) {
        this.router.navigate(['/login']);
      }
      return of(isAuth);
    }
  }
}
```

#### 3. Измените маршрутизацию, чтобы проверять Telegram авторизацию

```typescript
// src/app/app-routing.module.ts
import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';
import { AuthGuard } from './guards/auth.guard';
import { TelegramAuthGuard } from './guards/telegram-auth.guard';

const routes: Routes = [
  {
    path: '',
    redirectTo: 'home',
    pathMatch: 'full'
  },
  {
    path: 'home',
    loadChildren: () => import('./pages/home/home.module').then(m => m.HomePageModule),
    canActivate: [TelegramAuthGuard]
  },
  {
    path: 'profile',
    loadChildren: () => import('./pages/profile/profile.module').then(m => m.ProfilePageModule),
    canActivate: [TelegramAuthGuard]
  },
  // Оставляем маршруты для входа и регистрации, но они не будут показаны в Telegram WebApp
  {
    path: 'login',
    loadChildren: () => import('./pages/login/login.module').then(m => m.LoginPageModule)
  },
  {
    path: 'register',
    loadChildren: () => import('./pages/register/register.module').then(m => m.RegisterPageModule)
  },
];

@NgModule({
  imports: [
    RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules })
  ],
  exports: [RouterModule]
})
export class AppRoutingModule { }
```

#### 4. Модифицируйте страницу входа, чтобы учитывать Telegram

```typescript
// src/app/pages/login/login.page.ts
import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { TelegramService } from '../../services/telegram.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
})
export class LoginPage implements OnInit {
  constructor(
    private telegramService: TelegramService,
    private router: Router
  ) {}

  ngOnInit() {
    // Если мы в Telegram WebApp, пробуем авторизоваться и перенаправить
    if (this.telegramService.isInTelegramWebApp()) {
      this.telegramService.autoLogin().subscribe(success => {
        if (success) {
          this.router.navigate(['/home']);
        }
      });
    }
  }
}
```

#### 5. Настройте app.component.ts для инициализации Telegram

```typescript
// src/app/app.component.ts
import { Component } from '@angular/core';
import { Platform } from '@ionic/angular';
import { TelegramService } from './services/telegram.service';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss'],
})
export class AppComponent {
  constructor(
    private platform: Platform,
    private telegramService: TelegramService
  ) {
    this.initializeApp();
  }

  initializeApp() {
    this.platform.ready().then(() => {
      // Если приложение открыто в Telegram WebApp, автоматически входим
      if (this.telegramService.isInTelegramWebApp()) {
        this.telegramService.autoLogin().subscribe();
      }
    });
  }
}
```

#### 6. Добавьте настройки в файл окружения

```typescript
// src/environments/environment.ts
export const environment = {
  production: false,
  apiUrl: 'https://fizio.online/api',
  telegramWebAppUrl: 'https://fizio.online/telegram/webapp'
};
```

### Сборка и размещение Ionic приложения

1. Соберите Ionic приложение:
```bash
ionic build --prod
```

2. Скопируйте содержимое папки `www` (или `dist`) в директорию `public/webapp` вашего Laravel проекта:
```bash
cp -r www/* public/webapp/
```

## Тестирование и отладка

1. Для локального тестирования можно использовать [ngrok](https://ngrok.com/) для создания туннеля:
```bash
ngrok http 8000
```

2. Используйте полученный URL для настройки webhook бота:
```
https://your-ngrok-domain.ngrok.io/api/telegram/setup-webhook
```

3. Логи Telegram webhook можно найти в `storage/logs/laravel.log`

## Дополнительная информация

- [Документация Telegram Bot API](https://core.telegram.org/bots/api)
- [Документация Telegram WebApp](https://core.telegram.org/bots/webapps)
- [Руководство по использованию Telegram WebApp](https://core.telegram.org/bots/webapps#implementing-mini-apps) 