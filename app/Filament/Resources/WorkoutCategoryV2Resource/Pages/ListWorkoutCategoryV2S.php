<?php

namespace App\Filament\Resources\WorkoutCategoryV2Resource\Pages;

use App\Filament\Resources\WorkoutCategoryV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutCategoryV2S extends ListRecords
{
    protected static string $resource = WorkoutCategoryV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
