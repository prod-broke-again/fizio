<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImportOpenFoodFacts extends Command
{
    protected $signature = 'openfoodfacts:import {file : Path to CSV file} {--limit= : Limit number of records to import} {--cis-only : Import only CIS products} {--russian-only : Import only Russian products} {--chunk-size=500 : Size of each chunk} {--resume : Resume from last position} {--start-line= : Start from specific line number} {--reset : Reset progress and start from beginning}';
    protected $description = 'Import Open Food Facts data from CSV file with chunking and resume capability';

    private $progressFile = 'import_progress.json';
    private $statsFile = 'import_stats.json';

    public function handle()
    {
        $file = $this->argument('file');
        $limit = $this->option('limit');
        $cisOnly = $this->option('cis-only');
        $russianOnly = $this->option('russian-only');
        $chunkSize = (int) $this->option('chunk-size');
        $resume = $this->option('resume');
        $startLine = $this->option('start-line');
        $reset = $this->option('reset');
        
        if (!file_exists($file)) {
            $this->error("File {$file} not found!");
            return 1;
        }

        $this->info("Starting import from {$file}...");
        
        if ($cisOnly) {
            $this->info("Filtering: CIS products only");
        }
        
        if ($russianOnly) {
            $this->info("Filtering: Russian products only");
        }
        
        $this->info("Chunk size: {$chunkSize}");
        
        // Если указан флаг --reset, очищаем прогресс
        if ($reset) {
            $this->clearProgress();
            $this->info("Progress reset - starting from beginning");
        }
        
        // Загружаем прогресс
        $progress = $this->loadProgress();
        $stats = $this->loadStats();
        
        $startPosition = $startLine ?: ($resume && !$reset ? ($progress['last_line'] ?? 1) : 1);
        
        $this->info("Starting from line: {$startPosition}");
        
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle, 0, "\t");
        
        $this->info("Found " . count($headers) . " columns");
        
        // Пропускаем строки до начальной позиции
        for ($i = 1; $i < $startPosition; $i++) {
            fgetcsv($handle, 0, "\t");
        }
        
        $currentLine = $startPosition;
        $totalProcessed = $reset ? 0 : ($stats['total_processed'] ?? 0);
        $totalImported = $reset ? 0 : ($stats['total_imported'] ?? 0);
        $totalSkipped = $reset ? 0 : ($stats['total_skipped'] ?? 0);
        $chunkCount = 0;
        
        // Если используем --resume, сбрасываем счетчики для нового сегмента
        if ($resume && !$reset) {
            $totalProcessed = 0;
            $totalImported = 0;
            $totalSkipped = 0;
        }
        
        try {
            while (($data = fgetcsv($handle, 0, "\t")) !== false) {
                if ($limit && $totalProcessed >= $limit) {
                    $this->info("Reached limit: {$limit}");
                    break;
                }
                
                // Проверяем, что количество элементов совпадает с заголовками
                if (count($data) !== count($headers)) {
                    $this->warn("Skipping line {$currentLine}: mismatched columns (expected " . count($headers) . ", got " . count($data) . ")");
                    $totalSkipped++;
                    $currentLine++;
                    continue;
                }
                
                $productData = array_combine($headers, $data);
                
                // Проверяем, что array_combine не вернул false
                if ($productData === false) {
                    $this->warn("Skipping line {$currentLine}: failed to combine headers with data");
                    $totalSkipped++;
                    $currentLine++;
                    continue;
                }
                
                // Фильтрация товаров СНГ
                if ($cisOnly && !$this->isCISProduct($productData)) {
                    $totalSkipped++;
                    $currentLine++;
                    continue;
                }
                
                // Фильтрация только российских товаров
                if ($russianOnly && !$this->isRussianProduct($productData)) {
                    $totalSkipped++;
                    $currentLine++;
                    continue;
                }
                
                // Очищаем кеш для этого продукта
                Cache::forget("product_barcode_" . $productData['code']);
                
                $batch[] = [
                    'code' => $this->truncateString($productData['code'] ?? null, 50),
                    'product_name' => $this->truncateString($productData['product_name'] ?? null, 1000),
                    'generic_name' => $this->truncateString($productData['generic_name'] ?? null, 1000),
                    'brands' => $this->truncateString($productData['brands'] ?? null, 500),
                    'categories' => $this->truncateString($productData['categories'] ?? null, 1000),
                    'ingredients_text' => $this->truncateString($productData['ingredients_text'] ?? null, 2000),
                    'countries' => $this->truncateString($productData['countries'] ?? null, 500),
                    'quantity' => $this->truncateString($productData['quantity'] ?? null, 100),
                    'packaging' => $this->truncateString($productData['packaging'] ?? null, 1000),
                    'image_url' => $this->truncateString($productData['image_url'] ?? null, 500),
                    'image_small_url' => $this->truncateString($productData['image_small_url'] ?? null, 500),
                    'image_ingredients_url' => $this->truncateString($productData['image_ingredients_url'] ?? null, 500),
                    'image_nutrition_url' => $this->truncateString($productData['image_nutrition_url'] ?? null, 500),
                    'nutriscore_grade' => $this->truncateString($productData['nutriscore_grade'] ?? null, 10),
                    'nova_group' => $this->truncateString($productData['nova_group'] ?? null, 10),
                    'energy-kcal_100g' => $this->parseNumber($productData['energy-kcal_100g']),
                    'proteins_100g' => $this->parseNumber($productData['proteins_100g']),
                    'carbohydrates_100g' => $this->parseNumber($productData['carbohydrates_100g']),
                    'fat_100g' => $this->parseNumber($productData['fat_100g']),
                    'fiber_100g' => $this->parseNumber($productData['fiber_100g']),
                    'salt_100g' => $this->parseNumber($productData['salt_100g']),
                    'sugars_100g' => $this->parseNumber($productData['sugars_100g']),
                    'saturated-fat_100g' => $this->parseNumber($productData['saturated-fat_100g']),
                    'allergens' => $this->truncateString($productData['allergens'] ?? null, 1000),
                    'traces' => $this->truncateString($productData['traces'] ?? null, 1000),
                    'additives' => $this->truncateString($productData['additives'] ?? null, 1000),
                    'labels' => $this->truncateString($productData['labels'] ?? null, 1000),
                    'origins' => $this->truncateString($productData['origins'] ?? null, 1000),
                    'manufacturing_places' => $this->truncateString($productData['manufacturing_places'] ?? null, 1000),
                    'created_t' => $productData['created_t'] ? date('Y-m-d H:i:s', $productData['created_t']) : null,
                    'last_modified_t' => $productData['last_modified_t'] ? date('Y-m-d H:i:s', $productData['last_modified_t']) : null,
                    'unique_scans_n' => intval($productData['unique_scans_n'] ?? 0),
                    'completeness' => $this->parseNumber($productData['completeness']),
                ];
                
                $totalProcessed++;
                $totalImported++;
                
                // Сохраняем чанк
                if (count($batch) >= $chunkSize) {
                    try {
                        $this->importBatch($batch);
                        $batch = [];
                        $chunkCount++;
                        
                        // Сохраняем прогресс
                        $this->saveProgress($currentLine, $chunkCount);
                        $this->saveStats($totalProcessed, $totalImported, $totalSkipped);
                        
                        $this->info("Chunk {$chunkCount}: Processed {$totalProcessed}, imported {$totalImported}, skipped {$totalSkipped} (line {$currentLine})");
                        
                        // Небольшая пауза между чанками
                        usleep(100000); // 0.1 секунды
                    } catch (\Exception $e) {
                        $this->error("Error importing chunk {$chunkCount}: " . $e->getMessage());
                        $this->saveProgress($currentLine, $chunkCount);
                        $this->saveStats($totalProcessed, $totalImported, $totalSkipped);
                        throw $e;
                    }
                }
                
                $currentLine++;
            }
            
            // Импортируем оставшиеся записи
            if (!empty($batch)) {
                $this->importBatch($batch);
                $chunkCount++;
                $this->info("Final chunk {$chunkCount}: Processed {$totalProcessed}, imported {$totalImported}, skipped {$totalSkipped}");
            }
            
        } catch (\Exception $e) {
            $this->error("Error at line {$currentLine}: " . $e->getMessage());
            $this->saveProgress($currentLine, $chunkCount);
            $this->saveStats($totalProcessed, $totalImported, $totalSkipped);
            throw $e;
        }
        
        fclose($handle);
        $this->info("Import completed! Total processed: {$totalProcessed}, imported: {$totalImported}, skipped: {$totalSkipped}");
        
        // Очищаем кеш после импорта
        $this->clearCache();
        
        // Очищаем файлы прогресса только если импорт завершен полностью (достигнут конец файла)
        // НЕ очищаем при достижении лимита, чтобы можно было продолжить
        if (!$limit) {
            $this->clearProgress();
            $this->info("Progress files cleared");
        } else {
            $this->info("Progress saved for resume");
        }
    }
    
    private function importBatch($batch)
    {
        DB::table('products')->upsert($batch, ['code'], [
            'product_name', 'generic_name', 'brands', 'categories',
            'ingredients_text', 'countries', 'quantity', 'packaging',
            'image_url', 'image_small_url', 'image_ingredients_url', 'image_nutrition_url',
            'nutriscore_grade', 'nova_group', 'energy-kcal_100g', 'proteins_100g',
            'carbohydrates_100g', 'fat_100g', 'fiber_100g', 'salt_100g',
            'sugars_100g', 'saturated-fat_100g', 'allergens', 'traces',
            'additives', 'labels', 'origins', 'manufacturing_places',
            'created_t', 'last_modified_t', 'unique_scans_n', 'completeness'
        ]);
    }
    
    private function parseNumber($value)
    {
        if (empty($value) || $value === '') {
            return null;
        }
        
        $value = str_replace(',', '.', $value);
        if (!is_numeric($value)) {
            return null;
        }
        
        $number = floatval($value);
        
        // Проверяем на разумные пределы (очень большие значения могут быть ошибками)
        if ($number > 999999999999) {
            return null;
        }
        
        if ($number < -999999999999) {
            return null;
        }
        
        return $number;
    }
    
    private function isCISProduct($productData)
    {
        $cisCountries = ['russia', 'ukraine', 'belarus', 'kazakhstan', 'moldova'];
        $countries = strtolower($productData['countries'] ?? '');
        $manufacturing = strtolower($productData['manufacturing_places'] ?? '');
        $origins = strtolower($productData['origins'] ?? '');
        
        // Проверяем, есть ли страна СНГ
        $hasCISCountry = false;
        foreach ($cisCountries as $country) {
            if (str_contains($countries, $country) || 
                str_contains($manufacturing, $country) || 
                str_contains($origins, $country)) {
                $hasCISCountry = true;
                break;
            }
        }
        
        if (!$hasCISCountry) {
            return false;
        }
        
        // Исключаем товары с турецкими, армянскими и другими языками
        $productName = strtolower($productData['product_name'] ?? '');
        $excludedTerms = ['турецк', 'армянск', 'türk', 'հայկական', 'azerbaijan', 'georgia'];
        
        foreach ($excludedTerms as $term) {
            if (str_contains($productName, $term)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function isRussianProduct($productData)
    {
        $countries = strtolower($productData['countries'] ?? '');
        $manufacturing = strtolower($productData['manufacturing_places'] ?? '');
        $origins = strtolower($productData['origins'] ?? '');
        
        return str_contains($countries, 'russia') || 
               str_contains($manufacturing, 'russia') || 
               str_contains($origins, 'russia');
    }
    
    private function loadProgress()
    {
        if (Storage::exists($this->progressFile)) {
            return json_decode(Storage::get($this->progressFile), true) ?? [];
        }
        return [];
    }
    
    private function saveProgress($line, $chunk)
    {
        $progress = [
            'last_line' => $line,
            'last_chunk' => $chunk,
            'updated_at' => now()->toISOString()
        ];
        
        Storage::put($this->progressFile, json_encode($progress));
    }
    
    private function loadStats()
    {
        if (Storage::exists($this->statsFile)) {
            return json_decode(Storage::get($this->statsFile), true) ?? [];
        }
        return [];
    }
    
    private function saveStats($processed, $imported, $skipped)
    {
        // Загружаем существующую статистику
        $existingStats = $this->loadStats();
        
        $stats = [
            'total_processed' => ($existingStats['total_processed'] ?? 0) + $processed,
            'total_imported' => ($existingStats['total_imported'] ?? 0) + $imported,
            'total_skipped' => ($existingStats['total_skipped'] ?? 0) + $skipped,
            'updated_at' => now()->toISOString()
        ];
        
        Storage::put($this->statsFile, json_encode($stats));
    }
    
    private function clearProgress()
    {
        Storage::delete($this->progressFile);
        Storage::delete($this->statsFile);
        $this->info("Progress files cleared");
    }
    
    private function clearCache()
    {
        // Очищаем кеш для методов поиска
        Cache::forget('cis_products_20');
        Cache::forget('russian_products_20');
        Cache::forget('cis_manufactured_20');
        Cache::forget('popular_products_20');
        
        $this->info("Cache cleared");
    }

    private function truncateString($string, $length)
    {
        if (mb_strlen($string) <= $length) {
            return $string;
        }
        return mb_substr($string, 0, $length);
    }
}
