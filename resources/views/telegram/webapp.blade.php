<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fizio WebApp</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--tg-theme-bg-color, #ffffff);
            color: var(--tg-theme-text-color, #000000);
        }
        
        #app {
            height: 100vh;
            width: 100%;
            overflow: hidden;
        }
        
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100%;
            flex-direction: column;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--tg-theme-button-color, #2AABEE);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="loading">
            <div class="loading-spinner"></div>
            <div>Загрузка приложения...</div>
        </div>
    </div>
    
    <script>
        // Инициализация Telegram WebApp
        const tg = window.Telegram.WebApp;
        
        // Сообщаем Telegram, что приложение готово к отображению
        tg.ready();
        
        // Устанавливаем основной цвет темы
        document.documentElement.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color);
        document.documentElement.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color);
        document.documentElement.style.setProperty('--tg-theme-button-color', tg.themeParams.button_color);
        
        // Получаем данные пользователя из Telegram WebApp
        const user = tg.initDataUnsafe.user;
        
        // Функция для перенаправления на Angular/Vue/React приложение с данными Telegram
        function redirectToApp() {
            // Здесь можно перенаправить на фронтенд-приложение с передачей данных
            // Например, загрузить скрипты приложения и передать им данные инициализации
            
            // Путь к статическим файлам Ionic приложения (предполагается, что они размещены в /public/webapp)
            const appScript = document.createElement('script');
            appScript.src = '/webapp/js/main.js';
            appScript.onload = function() {
                // После загрузки основного скрипта можно инициализировать приложение с данными Telegram
                if (window.initFizioApp) {
                    window.initFizioApp({
                        telegramUser: user,
                        initData: tg.initData
                    });
                }
            };
            
            // Добавляем скрипты и стили приложения
            const appCss = document.createElement('link');
            appCss.rel = 'stylesheet';
            appCss.href = '/webapp/css/main.css';
            
            document.head.appendChild(appCss);
            document.body.appendChild(appScript);
        }
        
        // Запускаем перенаправление после небольшой задержки (для анимации загрузки)
        setTimeout(redirectToApp, 1000);
    </script>
</body>
</html> 