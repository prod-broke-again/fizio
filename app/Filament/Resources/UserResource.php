<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Пользователь';
    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Управление';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('avatar')
                            ->label('Аватар')
                            ->image()
                            ->directory('avatars')
                            ->disk('public') // <--- Указываем публичный диск
                            ->avatar()
                            ->helperText('Загрузите изображение для аватара пользователя.')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : '')
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText('Оставьте поле пустым, чтобы не изменять пароль.'),
                        Forms\Components\Select::make('roles')
                            ->label('Роли')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                ImageColumn::make('avatar')
                    ->label('Аватар')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата регистрации')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email подтвержден')
                    ->nullable()
                    ->trueLabel('Подтвержден')
                    ->falseLabel('Не подтвержден'),
            ])
            ->actions([
                Tables\Actions\Action::make('impersonate')
                    ->label('Войти под пользователем')
                    ->icon('heroicon-o-user')
                    ->color('warning')
                    ->url(fn ($record) => route('filament.admin.admin.impersonate', $record->id))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->id !== auth()->id()), // Не показывать для текущего пользователя
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
