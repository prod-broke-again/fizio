<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class UpdateAssetsInSpa extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'assets:update-spa {--force : Принудительно обновить без подтверждения} {--clean : Удалить старые неиспользуемые файлы ассетов}';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Обновляет пути к ассетам в spa.blade.php на основе данных из index.html и удаляет старые ассеты. Флаг --force пропускает все подтверждения.';

    /**
     * Путь к файлу index.html
     *
     * @var string
     */
    protected string $indexPath = 'public/index.html';

    /**
     * Путь к файлу spa.blade.php
     *
     * @var string
     */
    protected string $spaPath = 'resources/views/spa.blade.php';

    /**
     * Путь к директории с ассетами
     *
     * @var string
     */
    protected string $assetsPath = 'public/assets';

    /**
     * Шаблоны для идентификации типов файлов
     *
     * @var array
     */
    protected array $filePatterns = [
        'js' => [
            '/^index-.*\.js$/',
            '/^index-legacy-.*\.js$/',
            '/^polyfills-legacy-.*\.js$/',
            '/^status-tap-.*\.js$/',
            '/^swipe-back-.*\.js$/',
            '/^focus-visible-.*\.js$/',
            '/^md\.transition-.*\.js$/',
            '/^index\d*-.*\.js$/',
            '/^input-shims-.*\.js$/',
            '/^ios\.transition-.*\.js$/',
        ],
        'css' => [
            '/^index-.*\.css$/',
        ],
        'images' => [
            '/^.*\.png$/',
            '/^.*\.jpg$/',
            '/^.*\.jpeg$/',
            '/^.*\.gif$/',
            '/^.*\.svg$/',
            '/^.*\.webp$/',
            '/^.*\.ico$/',
        ],
        'fonts' => [
            '/^.*\.woff$/',
            '/^.*\.woff2$/',
            '/^.*\.ttf$/',
            '/^.*\.eot$/',
        ],
    ];

    /**
     * Выполнение консольной команды.
     */
    public function handle(): int
    {
        $this->info('Запуск обновления ассетов в файле spa.blade.php');

        // Проверяем существование файлов
        if (!file_exists($this->indexPath)) {
            $this->error("Файл {$this->indexPath} не найден.");
            return 1;
        }

        if (!file_exists($this->spaPath)) {
            $this->error("Файл {$this->spaPath} не найден.");
            return 1;
        }

        if (!file_exists($this->assetsPath)) {
            $this->error("Директория {$this->assetsPath} не найдена.");
            return 1;
        }

        // Читаем содержимое файлов
        $indexContent = file_get_contents($this->indexPath);
        $spaContent = file_get_contents($this->spaPath);

        // Извлекаем пути к ассетам из index.html
        $mainJsPattern = '/src="\/assets\/index-([^"]+)\.js"/';
        $cssPattern = '/href="\/assets\/index-([^"]+)\.css"/';
        $legacyJsPattern = '/data-src="(\/)?assets\/index-legacy-([^"]+)\.js"/';
        $polyfillsPattern = '/src="(\/)?assets\/polyfills-legacy-([^"]+)\.js"/';

        if (!preg_match($mainJsPattern, $indexContent, $mainJsMatches)) {
            $this->error("Не удалось найти путь к основному JS файлу в index.html");
            return 1;
        }

        if (!preg_match($cssPattern, $indexContent, $cssMatches)) {
            $this->error("Не удалось найти путь к CSS файлу в index.html");
            return 1;
        }

        if (!preg_match($legacyJsPattern, $indexContent, $legacyJsMatches)) {
            $this->error("Не удалось найти путь к legacy JS файлу в index.html");
            return 1;
        }

        // Поиск пути к полифилам (опционально)
        $polyfillsMatches = [];
        preg_match($polyfillsPattern, $indexContent, $polyfillsMatches);

        // Формируем новые пути к ассетам
        $newMainJs = 'index-' . $mainJsMatches[1] . '.js';
        $newCss = 'index-' . $cssMatches[1] . '.css';
        $newLegacyJs = 'index-legacy-' . $legacyJsMatches[2] . '.js';
        $newPolyfills = isset($polyfillsMatches[2]) ? 'polyfills-legacy-' . $polyfillsMatches[2] . '.js' : null;

        // Находим текущие пути в spa.blade.php
        $currentMainJsPattern = '/src="\/assets\/index-([^"]+)\.js"/';
        $currentCssPattern = '/href="\/assets\/index-([^"]+)\.css"/';
        $currentLegacyJsPattern = '/data-src="(\/)?assets\/index-legacy-([^"]+)\.js"/';
        $currentPolyfillsPattern = '/src="(\/)?assets\/polyfills-legacy-([^"]+)\.js"/';

        if (!preg_match($currentMainJsPattern, $spaContent, $currentMainJsMatches)) {
            $this->error("Не удалось найти путь к основному JS файлу в spa.blade.php");
            return 1;
        }

        if (!preg_match($currentCssPattern, $spaContent, $currentCssMatches)) {
            $this->error("Не удалось найти путь к CSS файлу в spa.blade.php");
            return 1;
        }

        if (!preg_match($currentLegacyJsPattern, $spaContent, $currentLegacyJsMatches)) {
            $this->error("Не удалось найти путь к legacy JS файлу в spa.blade.php");
            return 1;
        }

        // Поиск пути к полифилам (опционально)
        $currentPolyfillsMatches = [];
        preg_match($currentPolyfillsPattern, $spaContent, $currentPolyfillsMatches);

        // Формируем текущие пути к ассетам
        $currentMainJs = 'index-' . $currentMainJsMatches[1] . '.js';
        $currentCss = 'index-' . $currentCssMatches[1] . '.css';
        $currentLegacyJs = 'index-legacy-' . $currentLegacyJsMatches[2] . '.js';
        $currentPolyfills = isset($currentPolyfillsMatches[2]) ? 'polyfills-legacy-' . $currentPolyfillsMatches[2] . '.js' : null;

        // Собираем старые файлы для возможного удаления
        $oldFiles = [
            $currentMainJs,
            $currentCss,
            $currentLegacyJs
        ];

        if ($currentPolyfills) {
            $oldFiles[] = $currentPolyfills;
        }

        // Собираем новые файлы для исключения из удаления
        $newFiles = [
            $newMainJs,
            $newCss,
            $newLegacyJs
        ];

        if ($newPolyfills) {
            $newFiles[] = $newPolyfills;
        }

        // Получаем дополнительные файлы из index.html
        $additionalJsFiles = $this->extractAdditionalAssets($indexContent);
        $newFiles = array_merge($newFiles, $additionalJsFiles);

        // Проверяем необходимость обновления
        if ($currentMainJs === $newMainJs && $currentCss === $newCss && $currentLegacyJs === $newLegacyJs
            && ($currentPolyfills === $newPolyfills || (!$currentPolyfills && !$newPolyfills))) {
            $this->info("Пути к ассетам уже актуальны. Обновление не требуется.");

            // Если указан флаг --clean, проверяем есть ли старые файлы для удаления
            if ($this->option('clean')) {
                $this->cleanupOldAssets($newFiles);
            }

            return 0;
        }

        // Выводим информацию об изменениях
        $this->info("Найдены следующие изменения:");

        if ($currentMainJs !== $newMainJs) {
            $this->info("- JS: {$currentMainJs} -> {$newMainJs}");
        }

        if ($currentCss !== $newCss) {
            $this->info("- CSS: {$currentCss} -> {$newCss}");
        }

        if ($currentLegacyJs !== $newLegacyJs) {
            $this->info("- Legacy JS: {$currentLegacyJs} -> {$newLegacyJs}");
        }

        if ($currentPolyfills !== $newPolyfills) {
            $this->info("- Polyfills: " . ($currentPolyfills ?: 'отсутствует') . " -> " . ($newPolyfills ?: 'отсутствует'));
        }

        // Флаг --force пропускает все подтверждения пользователя
        // Запрашиваем подтверждение, если не указан флаг --force
        if (!$this->option('force') && !$this->confirm('Вы уверены, что хотите обновить пути к ассетам?')) {
            $this->info('Операция отменена.');
            return 0;
        }

        // Заменяем пути в spa.blade.php
        $updatedSpaContent = preg_replace(
            "/src=\"\/assets\/{$currentMainJs}\"/",
            "src=\"/assets/{$newMainJs}\"",
            $spaContent
        );

        $updatedSpaContent = preg_replace(
            "/href=\"\/assets\/{$currentCss}\"/",
            "href=\"/assets/{$newCss}\"",
            $updatedSpaContent
        );

        // Определяем формат пути (с / или без)
        if (preg_match('/data-src="\/assets/', $spaContent)) {
            // С начальным слешем
            $updatedSpaContent = preg_replace(
                "/data-src=\"\/assets\/{$currentLegacyJs}\"/",
                "data-src=\"/assets/{$newLegacyJs}\"",
                $updatedSpaContent
            );
            
            // Обновляем полифилы если они есть
            if ($currentPolyfills && $newPolyfills) {
                $updatedSpaContent = preg_replace(
                    "/src=\"\/assets\/{$currentPolyfills}\"/",
                    "src=\"/assets/{$newPolyfills}\"",
                    $updatedSpaContent
                );
            }
        } else {
            // Без начального слеша
            $updatedSpaContent = preg_replace(
                "/data-src=\"assets\/{$currentLegacyJs}\"/",
                "data-src=\"assets/{$newLegacyJs}\"",
                $updatedSpaContent
            );
            
            // Обновляем полифилы если они есть
            if ($currentPolyfills && $newPolyfills) {
                $updatedSpaContent = preg_replace(
                    "/src=\"assets\/{$currentPolyfills}\"/",
                    "src=\"assets/{$newPolyfills}\"",
                    $updatedSpaContent
                );
            }
        }

        // Сохраняем обновленный файл
        file_put_contents($this->spaPath, $updatedSpaContent);

        // Логируем операцию
        $logData = [
            'js' => "{$currentMainJs} -> {$newMainJs}",
            'css' => "{$currentCss} -> {$newCss}",
            'legacy_js' => "{$currentLegacyJs} -> {$newLegacyJs}"
        ];

        if ($currentPolyfills && $newPolyfills) {
            $logData['polyfills'] = "{$currentPolyfills} -> {$newPolyfills}";
        }

        Log::info('Обновлены пути к ассетам в spa.blade.php', $logData);

        $this->info('Пути к ассетам успешно обновлены!');

        // Если указан флаг --clean, удаляем старые файлы
        if ($this->option('clean')) {
            $this->cleanupOldAssets($newFiles, $oldFiles);
        }

        return 0;
    }

    /**
     * Извлекает дополнительные ассеты из файла index.html или spa.blade.php
     *
     * @param string $content Содержимое файла
     * @return array Список найденных файлов
     */
    protected function extractAdditionalAssets($content)
    {
        $foundFiles = [];

        // Ищем все ссылки на JavaScript файлы
        preg_match_all('/src="\/assets\/([^"]+\.js)"/i', $content, $jsMatches);
        if (!empty($jsMatches[1])) {
            $foundFiles = array_merge($foundFiles, $jsMatches[1]);
        }
        
        // Ищем JavaScript файлы без начального слеша
        preg_match_all('/src="assets\/([^"]+\.js)"/i', $content, $jsMatchesNoSlash);
        if (!empty($jsMatchesNoSlash[1])) {
            $foundFiles = array_merge($foundFiles, $jsMatchesNoSlash[1]);
        }

        // Ищем все ссылки на CSS файлы
        preg_match_all('/href="\/assets\/([^"]+\.css)"/i', $content, $cssMatches);
        if (!empty($cssMatches[1])) {
            $foundFiles = array_merge($foundFiles, $cssMatches[1]);
        }
        
        // Ищем CSS файлы без начального слеша
        preg_match_all('/href="assets\/([^"]+\.css)"/i', $content, $cssMatchesNoSlash);
        if (!empty($cssMatchesNoSlash[1])) {
            $foundFiles = array_merge($foundFiles, $cssMatchesNoSlash[1]);
        }

        // Ищем все ссылки на изображения (могут быть в стилях)
        preg_match_all('/url\(["\']\??\/assets\/([^"\')]+)["\']?\)/i', $content, $imgMatches);
        if (!empty($imgMatches[1])) {
            $foundFiles = array_merge($foundFiles, $imgMatches[1]);
        }
        
        // Ищем изображения без начального слеша
        preg_match_all('/url\(["\']\??assets\/([^"\')]+)["\']?\)/i', $content, $imgMatchesNoSlash);
        if (!empty($imgMatchesNoSlash[1])) {
            $foundFiles = array_merge($foundFiles, $imgMatchesNoSlash[1]);
        }

        // Ищем все ссылки на полифилы
        preg_match_all('/data-src="\/assets\/([^"]+)"/i', $content, $polyfillMatches);
        if (!empty($polyfillMatches[1])) {
            $foundFiles = array_merge($foundFiles, $polyfillMatches[1]);
        }
        
        // Ищем полифилы без начального слеша
        preg_match_all('/data-src="assets\/([^"]+)"/i', $content, $polyfillMatchesNoSlash);
        if (!empty($polyfillMatchesNoSlash[1])) {
            $foundFiles = array_merge($foundFiles, $polyfillMatchesNoSlash[1]);
        }

        return $foundFiles;
    }

    /**
     * Удаляет старые неиспользуемые файлы ассетов
     *
     * @param array $newFiles Список новых файлов, которые не нужно удалять
     * @param array|null $oldFiles Список старых файлов, которые точно нужно проверить
     * @return void
     */
    protected function cleanupOldAssets(array $newFiles, array $oldFiles = null)
    {
        $this->info('Поиск старых неиспользуемых файлов ассетов...');

        $filesToDelete = [];
        $assetsDir = $this->assetsPath;

        if (!file_exists($assetsDir) || !is_dir($assetsDir)) {
            $this->error("Директория ассетов {$assetsDir} не найдена.");
            return;
        }

        // Получаем список всех файлов в директории ассетов
        $allFiles = scandir($assetsDir);
        $allPatterns = array_merge(
            $this->filePatterns['js'],
            $this->filePatterns['css'],
            $this->filePatterns['images'],
            $this->filePatterns['fonts']
        );

        // Функция для проверки хэша в имени файла
        $getHashFromFilename = function($filename) {
            // Паттерн для извлечения хэша из имени файла (формат: name-HASH.ext)
            if (preg_match('/^([^-]+)-([A-Za-z0-9_]+)\.([^\.]+)$/', $filename, $matches)) {
                return [
                    'base' => $matches[1],
                    'hash' => $matches[2],
                    'ext' => $matches[3]
                ];
            }
            return null;
        };

        // Группируем файлы по базовому имени
        $fileGroups = [];
        foreach ($allFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fileInfo = $getHashFromFilename($file);
            if ($fileInfo && !in_array($file, $newFiles)) {
                $key = $fileInfo['base'] . '.' . $fileInfo['ext'];
                if (!isset($fileGroups[$key])) {
                    $fileGroups[$key] = [];
                }
                $fileGroups[$key][] = $file;
            }
        }

        // Для каждой группы файлов оставляем только новый
        foreach ($fileGroups as $baseFile => $files) {
            if (count($files) <= 1) {
                continue; // Если только один файл, то нечего удалять
            }

            // Определяем какие файлы нужно сохранить
            $filesToKeep = array_intersect($files, $newFiles);
            $filesToRemove = array_diff($files, $filesToKeep);

            // Если передан список старых файлов, фильтруем по нему
            if ($oldFiles !== null) {
                $filesToRemove = array_intersect($filesToRemove, $oldFiles);
            }

            foreach ($filesToRemove as $file) {
                $filesToDelete[] = $file;
            }
        }

        // Проверяем дополнительно файлы без группировки
        foreach ($allFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            // Проверяем соответствие файла одному из паттернов
            $matchesPattern = false;
            foreach ($allPatterns as $pattern) {
                if (preg_match($pattern, $file)) {
                    $matchesPattern = true;
                    break;
                }
            }

            // Если файл соответствует паттерну, не входит в группы, и не является новым файлом
            if ($matchesPattern && !in_array($file, $newFiles) && !in_array($file, $filesToDelete)) {
                // Если это один из дополнительных JS файлов и его нет в списке новых, добавляем в список на удаление
                $fileInfo = $getHashFromFilename($file);
                if ($fileInfo) {
                    $baseFile = $fileInfo['base'];
                    // Проверяем известные базовые имена файлов
                    $knownBaseFiles = [
                        'status-tap', 'swipe-back', 'focus-visible', 'md.transition',
                        'input-shims', 'ios.transition'
                    ];

                    // Если это известный базовый файл, проверяем есть ли более новая версия
                    if (in_array($baseFile, $knownBaseFiles)) {
                        $hasNewer = false;
                        foreach ($newFiles as $newFile) {
                            if (strpos($newFile, $baseFile . '-') === 0) {
                                $hasNewer = true;
                                break;
                            }
                        }

                        if ($hasNewer) {
                            $filesToDelete[] = $file;
                        }
                    }
                    // Для файлов изображений, проверяем по имени без хэша
                    elseif (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|ico)$/i', $file)) {
                        // Файлы изображений добавляем только если явно указаны в списке старых
                        if ($oldFiles !== null && in_array($file, $oldFiles)) {
                            $filesToDelete[] = $file;
                        }
                    }
                }
            }
        }

        if (empty($filesToDelete)) {
            $this->info('Старые неиспользуемые файлы ассетов не найдены.');
            return;
        }

        $this->info('Найдены следующие старые неиспользуемые файлы ассетов:');
        foreach ($filesToDelete as $file) {
            $this->info("- {$file}");
        }

        // Флаг --force пропускает подтверждение удаления файлов
        if (!$this->option('force') && !$this->confirm('Вы уверены, что хотите удалить эти файлы?', true)) {
            $this->info('Удаление файлов отменено.');
            return;
        }

        $deletedCount = 0;
        $errorCount = 0;

        foreach ($filesToDelete as $file) {
            $filePath = $assetsDir . '/' . $file;
            try {
                if (File::exists($filePath)) {
                    File::delete($filePath);
                    $this->info("Удален файл: {$file}");
                    $deletedCount++;

                    // Логируем удаление
                    Log::info("Удален старый файл ассетов: {$file}");
                } else {
                    $this->warn("Файл не найден: {$file}");
                }
            } catch (\Exception $e) {
                $this->error("Ошибка при удалении файла {$file}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->info("Удаление завершено. Удалено файлов: {$deletedCount}, ошибок: {$errorCount}");
    }
}
