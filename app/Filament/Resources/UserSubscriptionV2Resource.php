<?php

namespace App\Filament\Resources;


use App\Filament\Resources\UserSubscriptionV2Resource\Pages;
use App\Filament\Resources\UserSubscriptionV2Resource\RelationManagers;
use App\Models\User;
use App\Models\UserSubscriptionV2;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserSubscriptionV2Resource extends Resource
{
    protected static ?string $model = UserSubscriptionV2::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationGroup = 'Тренировки V2';
    
    protected static ?string $navigationLabel = 'Подписки пользователей';
    
    protected static ?string $modelLabel = 'Подписка пользователя';
    
    protected static ?string $pluralModelLabel = 'Подписки пользователей';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Пользователь')
                            ->options(User::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('subscription_type')
                            ->label('Тип подписки')
                            ->options([
                                'monthly' => 'Месячная',
                                'yearly' => 'Годовая',
                                'lifetime' => 'Пожизненная',
                            ])
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state === 'lifetime') {
                                    $set('expires_at', null);
                                }
                            }),
                        
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'active' => 'Активная',
                                'expired' => 'Истекшая',
                                'cancelled' => 'Отмененная',
                            ])
                            ->required()
                            ->searchable()
                            ->default('active'),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Дата истечения')
                            ->visible(fn (callable $get) => $get('subscription_type') !== 'lifetime')
                            ->required(fn (callable $get) => $get('subscription_type') !== 'lifetime')
                            ->helperText('Не требуется для пожизненной подписки'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Заметки')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('subscription_type')
                    ->label('Тип подписки')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'monthly' => 'info',
                        'yearly' => 'warning',
                        'lifetime' => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state): string => match ($state?->value ?? $state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Истекает')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->isSubscriptionExpired() ? 'danger' : 'success')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->subscription_type === 'lifetime' ? 'Никогда' : $record->expires_at?->format('d.m.Y H:i')),
                
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Осталось дней')
                    ->getStateUsing(fn ($record) => $record->subscription_type === 'lifetime' ? '∞' : $record->daysRemaining())
                    ->badge()
                    ->color(fn ($record) => $record->isSubscriptionExpired() ? 'danger' : 'success'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_type')
                    ->label('Тип подписки')
                    ->options([
                        'monthly' => 'Месячная',
                        'yearly' => 'Годовая',
                        'lifetime' => 'Пожизненная',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активная',
                        'expired' => 'Истекшая',
                        'cancelled' => 'Отмененная',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Только активные')
                    ->default(true),
                
                Tables\Filters\Filter::make('expired')
                    ->label('Истекшие подписки')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now())->where('subscription_type', '!=', 'lifetime')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUserSubscriptionV2S::route('/'),
            'create' => Pages\CreateUserSubscriptionV2::route('/create'),
            'edit' => Pages\EditUserSubscriptionV2::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('user');
    }
}
