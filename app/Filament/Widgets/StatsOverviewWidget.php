<?php

namespace App\Filament\Widgets;

use App\Models\Meal;
use App\Models\User;
use App\Models\Workout;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Всего пользователей', User::count())
                ->description('Общее количество зарегистрированных пользователей')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Тренировок сегодня', Workout::whereDate('created_at', Carbon::today())->count())
                ->description('Количество тренировок за текущий день')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Приемов пищи сегодня', Meal::whereDate('created_at', Carbon::today())->count())
                ->description('Количество приемов пищи за текущий день')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
