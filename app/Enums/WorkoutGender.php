<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkoutGender: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    /**
     * Получить человекочитаемое название
     */
    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Мужские',
            self::FEMALE => 'Женские',
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
