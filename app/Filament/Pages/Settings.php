<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Настройки';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        // Для этого примера будем хранить настройки в json файле
        $this->data = file_exists(storage_path('app/settings.json'))
            ? json_decode(file_get_contents(storage_path('app/settings.json')), true)
            : [];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('spoonacular_api_key')
                    ->label('Spoonacular API Key')
                    ->placeholder('Введите ваш API ключ')
                    ->password()
                    ->required(),
                TextInput::make('fatsecret_client_id')
                    ->label('FatSecret Client ID')
                    ->placeholder('Введите ваш Client ID')
                    ->required(),
                TextInput::make('fatsecret_client_secret')
                    ->label('FatSecret Client Secret')
                    ->placeholder('Введите ваш Client Secret')
                    ->password()
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить изменения')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        file_put_contents(storage_path('app/settings.json'), json_encode($data));
        $this->form->fill($data);

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
