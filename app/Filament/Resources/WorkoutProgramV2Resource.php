<?php

namespace App\Filament\Resources;

use App\Enums\WorkoutDifficulty;
use App\Filament\Resources\WorkoutProgramV2Resource\Pages;
use App\Filament\Resources\WorkoutProgramV2Resource\RelationManagers;
use App\Models\WorkoutCategoryV2;
use App\Models\WorkoutProgramV2;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class WorkoutProgramV2Resource extends Resource
{
    protected static ?string $model = WorkoutProgramV2::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    
    protected static ?string $navigationGroup = 'Тренировки V2';
    
    protected static ?string $navigationLabel = 'Программы тренировок';
    
    protected static ?string $modelLabel = 'Программа тренировок';
    
    protected static ?string $pluralModelLabel = 'Программы тренировок';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Категория')
                            ->options(WorkoutCategoryV2::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Автоматически генерируется из названия'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('short_description')
                            ->label('Краткое описание')
                            ->maxLength(255)
                            ->helperText('Краткое описание для карточек'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Характеристики')
                    ->schema([
                        Forms\Components\Select::make('difficulty_level')
                            ->label('Уровень сложности')
                            ->options([
                                'beginner' => 'Начинающий',
                                'intermediate' => 'Средний',
                                'advanced' => 'Продвинутый',
                            ])
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\TextInput::make('duration_weeks')
                            ->label('Длительность (недели)')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(52)
                            ->step(1),
                        
                        Forms\Components\TextInput::make('calories_per_workout')
                            ->label('Калории за тренировку')
                            ->numeric()
                            ->minValue(0)
                            ->step(10)
                            ->suffix('ккал'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Настройки')
                    ->schema([
                        Forms\Components\Toggle::make('is_free')
                            ->label('Бесплатная')
                            ->default(true)
                            ->required()
                            ->helperText('Доступна без подписки'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('short_description')
                    ->label('Описание')
                    ->searchable()
                    ->limit(60)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('Сложность')
                    ->badge()
                    ->color(fn ($state): string => match ((string) $state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('duration_weeks')
                    ->label('Длительность')
                    ->numeric()
                    ->sortable()
                    ->suffix(' нед.')
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('calories_per_workout')
                    ->label('Калории')
                    ->numeric()
                    ->sortable()
                    ->suffix(' ккал')
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_free')
                    ->label('Бесплатная')
                    ->boolean()
                    ->trueIcon('heroicon-o-gift')
                    ->falseIcon('heroicon-o-credit-card')
                    ->trueColor('success')
                    ->falseColor('warning'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('workout_exercises_count')
                    ->label('Упражнений')
                    ->counts('workout_exercises')
                    ->badge()
                    ->color('info'),
                
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Категория')
                    ->options(WorkoutCategoryV2::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('Сложность')
                    ->options([
                        'beginner' => 'Начинающий',
                        'intermediate' => 'Средний',
                        'advanced' => 'Продвинутый',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('Только бесплатные'),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Только активные')
                    ->default(true),
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
            ->defaultSort('sort_order', 'asc')
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListWorkoutProgramV2S::route('/'),
            'create' => Pages\CreateWorkoutProgramV2::route('/create'),
            'edit' => Pages\EditWorkoutProgramV2::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('workout_exercises');
    }
}
