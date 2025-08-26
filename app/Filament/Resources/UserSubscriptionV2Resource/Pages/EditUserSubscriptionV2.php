<?php

namespace App\Filament\Resources\UserSubscriptionV2Resource\Pages;

use App\Filament\Resources\UserSubscriptionV2Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserSubscriptionV2 extends EditRecord
{
    protected static string $resource = UserSubscriptionV2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
