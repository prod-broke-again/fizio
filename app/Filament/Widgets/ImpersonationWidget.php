<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationWidget extends Widget
{
    protected static string $view = 'filament.widgets.impersonation-widget';
    
    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    public function isVisible(): bool
    {
        return Session::has('impersonate.original_user');
    }

    protected function getViewData(): array
    {
        return [
            'currentUser' => Auth::user(),
            'originalUserId' => Session::get('impersonate.original_user'),
        ];
    }
}
