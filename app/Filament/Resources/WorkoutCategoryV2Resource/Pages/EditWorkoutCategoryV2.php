<?php

namespace App\Filament\Resources\WorkoutCategoryV2Resource\Pages;

use App\Filament\Resources\WorkoutCategoryV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkoutCategoryV2 extends EditRecord
{
    protected static string $resource = WorkoutCategoryV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
