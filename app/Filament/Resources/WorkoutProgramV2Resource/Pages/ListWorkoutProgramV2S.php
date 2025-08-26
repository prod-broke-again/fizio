<?php

namespace App\Filament\Resources\WorkoutProgramV2Resource\Pages;

use App\Filament\Resources\WorkoutProgramV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutProgramV2S extends ListRecords
{
    protected static string $resource = WorkoutProgramV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
