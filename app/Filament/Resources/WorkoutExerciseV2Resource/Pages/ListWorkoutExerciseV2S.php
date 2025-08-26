<?php

namespace App\Filament\Resources\WorkoutExerciseV2Resource\Pages;

use App\Filament\Resources\WorkoutExerciseV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutExerciseV2S extends ListRecords
{
    protected static string $resource = WorkoutExerciseV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
