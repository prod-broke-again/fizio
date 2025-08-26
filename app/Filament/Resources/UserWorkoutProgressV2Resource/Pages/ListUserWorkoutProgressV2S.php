<?php

namespace App\Filament\Resources\UserWorkoutProgressV2Resource\Pages;

use App\Filament\Resources\UserWorkoutProgressV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserWorkoutProgressV2S extends ListRecords
{
    protected static string $resource = UserWorkoutProgressV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
