<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealResource\Pages;
use App\Models\Meal;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Tables\Table;

class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static ?string $modelLabel = 'Прием пищи';
    protected static ?string $pluralModelLabel = 'Приемы пищи';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Контент';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        FormSelect::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Дата')
                            ->required(),
                        FormSelect::make('type')
                            ->label('Тип')
                            ->options([
                                'breakfast' => 'Завтрак',
                                'lunch' => 'Обед',
                                'dinner' => 'Ужин',
                                'snack' => 'Перекус',
                            ])
                            ->required(),
                    ]),
                Section::make('Состав')
                    ->schema([
                        KeyValue::make('items')
                            ->label('Продукты')
                            ->keyLabel('Продукт')
                            ->valueLabel('Количество (в граммах)')
                            ->required()
                            ->helperText('Добавьте продукты и их вес в граммах.')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('calories')
                    ->label('Калории')
                    ->numeric()
                    ->sortable()
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Пользователь')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'breakfast' => 'Завтрак',
                        'lunch' => 'Обед',
                        'dinner' => 'Ужин',
                        'snack' => 'Перекус',
                    ]),
                Filter::make('date')
                    ->form([
                        DatePicker::make('created_from')->label('с'),
                        DatePicker::make('created_until')->label('по'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
            ])
            ->actions([
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
            'index' => Pages\ListMeals::route('/'),
            'create' => Pages\CreateMeal::route('/create'),
            'edit' => Pages\EditMeal::route('/{record}/edit'),
        ];
    }
}
