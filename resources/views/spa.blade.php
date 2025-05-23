<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <title>{{ config('app.name', 'Fizio') }}</title>

    <base href="/" />

    <meta name="color-scheme" content="light dark" />
    <meta
      name="viewport"
      content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"
    />
    <meta name="format-detection" content="telephone=no" />
    <meta name="msapplication-tap-highlight" content="no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" type="image/png" href="/favicon.png" />

    <!-- add to homescreen for ios -->
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Fizio') }}" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
      <script src="https://cdn.jsdelivr.net/npm/eruda"></script>
      <script>/*eruda.init();*/</script>
    <!-- Telegram WebApp script -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script type="module" crossorigin src="/assets/index-C2EnKAGv.js"></script>
    <link rel="stylesheet" crossorigin href="/assets/index-NmfDec2a.css">
    <script type="module">import.meta.url;import("_").catch(()=>1);(async function*(){})().next();if(location.protocol!="file:"){window.__vite_is_modern_browser=true}</script>
    <script type="module">!function(){if(window.__vite_is_modern_browser)return;console.warn("vite: loading legacy chunks, syntax error above and the same error below should be ignored");var e=document.getElementById("vite-legacy-polyfill"),n=document.createElement("script");n.src=e.src,n.onload=function(){System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))},document.body.appendChild(n)}();</script>

    <!-- Передача конфигурации Laravel в SPA -->
    <script>
      window.Laravel = {
        baseUrl: '{{ url('/') }}',
        apiUrl: '{{ url('/api') }}',
        csrfToken: '{{ csrf_token() }}',
        telegramWebAppUrl: '{{ config('telegram.webapp.url') }}'
      };

      // Инициализация Telegram WebApp
      document.addEventListener('DOMContentLoaded', function() {
        // Проверяем наличие Telegram WebApp
        if (window.Telegram && window.Telegram.WebApp) {
            const tg = window.Telegram.WebApp;

            // Расширяем приложение на весь экран
          tg.expand();

          // Используем официальный метод для отключения вертикальных свайпов
          if (typeof tg.disableVerticalSwipes === 'function') {
            tg.disableVerticalSwipes();
          } else {
            // Установка параметров MainButton для видимости того, что приложение загружено
            tg.MainButton.setText('Открыть');
            tg.MainButton.show();
            tg.MainButton.hide();
          }
        }
      });
    </script>
  </head>

  <body>
    <div id="app"></div>
    <script nomodule>!function(){var e=document,t=e.createElement("script");if(!("noModule"in t)&&"onbeforeload"in t){var n=!1;e.addEventListener("beforeload",(function(e){if(e.target===t)n=!0;else if(!e.target.hasAttribute("nomodule")||!n)return;e.preventDefault()}),!0),t.type="module",t.src=".",e.head.appendChild(t),t.remove()}}();</script>
    <script nomodule crossorigin id="vite-legacy-polyfill" src="/assets/polyfills-legacy-X2VkTPRm.js"></script>
    <script nomodule crossorigin id="vite-legacy-entry" data-src="/assets/index-legacy-ZzLEvZ4Y.js">System.import(document.getElementById('vite-legacy-entry').getAttribute('data-src'))</script>
  </body>
</html>
