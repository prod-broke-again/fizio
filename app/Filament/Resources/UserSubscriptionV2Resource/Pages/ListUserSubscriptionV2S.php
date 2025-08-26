<?php

namespace App\Filament\Resources\UserSubscriptionV2Resource\Pages;

use App\Filament\Resources\UserSubscriptionV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserSubscriptionV2S extends ListRecords
{
    protected static string $resource = UserSubscriptionV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
