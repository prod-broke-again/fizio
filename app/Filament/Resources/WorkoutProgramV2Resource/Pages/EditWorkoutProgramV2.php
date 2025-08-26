<?php

namespace App\Filament\Resources\WorkoutProgramV2Resource\Pages;

use App\Filament\Resources\WorkoutProgramV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkoutProgramV2 extends EditRecord
{
    protected static string $resource = WorkoutProgramV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
