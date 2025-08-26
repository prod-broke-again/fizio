<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkoutDifficulty: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

    /**
     * Получить человекочитаемое название
     */
    public function label(): string
    {
        return match ($this) {
            self::BEGINNER => 'Начинающий',
            self::INTERMEDIATE => 'Средний',
            self::ADVANCED => 'Продвинутый',
        };
    }

    /**
     * Получить все значения
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Получить все варианты с метками
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}
