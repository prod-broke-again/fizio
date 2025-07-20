<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class UserRegistrationsChart extends ChartWidget
{
    protected static ?string $heading = 'Регистрация новых пользователей';
    protected static string $color = 'info';


    protected function getData(): array
    {
        $data = User::select('created_at')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->get()
            ->groupBy(function ($user) {
                return Carbon::parse($user->created_at)->format('d.m');
            })
            ->map(function ($group) {
                return count($group);
            });

        return [
            'datasets' => [
                [
                    'label' => 'Новые пользователи',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
