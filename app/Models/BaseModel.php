<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Базовая модель для всех моделей V2
 */
abstract class BaseModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Первичный ключ модели
     */
    protected $primaryKey = 'id';

    /**
     * Атрибуты, которые должны быть приведены к нативным типам
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Получить уникальный идентификатор
     */
    public function getUniqueId(): string
    {
        return $this->id;
    }

    /**
     * Проверить, является ли запись активной
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

    /**
     * Активировать запись
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Деактивировать запись
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
