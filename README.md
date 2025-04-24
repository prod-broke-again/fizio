<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development/)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Fizio API

Серверная часть фитнес-приложения Fizio, разработанная на фреймворке Laravel.

## Описание

Fizio API предоставляет бэкенд-функциональность для мобильного фитнес-приложения на Ionic Vue. API обеспечивает регистрацию и аутентификацию пользователей, а также сохранение и получение информации о целях фитнеса.

## Требования

* PHP 8.1+
* Composer
* MySQL 8.0+
* Nginx/Apache

## Установка

1. Клонируйте репозиторий:
```bash
git clone https://github.com/yourusername/fizio.git
cd fizio
```

2. Установите зависимости:
```bash
composer install
```

3. Скопируйте файл окружения и настройте подключение к базе данных:
```bash
cp .env.example .env
```

4. Сгенерируйте ключ приложения:
```bash
php artisan key:generate
```

5. Выполните миграции:
```bash
php artisan migrate
```

6. Запустите веб-сервер:
```bash
php artisan serve
```

## Документация API

Подробная документация по API доступна в файле [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

### Основные эндпоинты:

* **POST /api/auth/register** - Регистрация нового пользователя
* **POST /api/auth/login** - Авторизация пользователя
* **POST /api/auth/logout** - Выход из системы
* **GET /api/user/profile** - Получение профиля пользователя
* **POST /api/user/fitness-goal** - Сохранение цели фитнеса
* **GET /api/user/fitness-goal** - Получение текущей цели фитнеса

## Тестирование

Инструкции по тестированию API доступны в файле [API_TESTING.md](API_TESTING.md).

Для быстрого тестирования всех эндпоинтов можно использовать скрипт:
```bash
chmod +x test_api.sh
./test_api.sh
```

Для запуска автоматических тестов используйте:
```bash
php artisan test
```

## Безопасность

Для защиты API используется Laravel Sanctum, который обеспечивает токен-аутентификацию для SPA (одностраничных приложений) и мобильных приложений.

## Лицензия

Этот проект распространяется под лицензией MIT. Подробности в файле [LICENSE.md](LICENSE.md).

## Контакты

При возникновении вопросов, пожалуйста, обращайтесь по адресу your-email@example.com.
