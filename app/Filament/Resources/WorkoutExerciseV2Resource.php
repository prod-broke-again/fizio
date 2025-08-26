<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkoutExerciseV2Resource\Pages;
use App\Filament\Resources\WorkoutExerciseV2Resource\RelationManagers;
use App\Models\WorkoutExerciseV2;
use App\Models\WorkoutProgramV2;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class WorkoutExerciseV2Resource extends Resource
{
    protected static ?string $model = WorkoutExerciseV2::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    
    protected static ?string $navigationGroup = 'Тренировки V2';
    
    protected static ?string $navigationLabel = 'Упражнения';
    
    protected static ?string $modelLabel = 'Упражнение';
    
    protected static ?string $pluralModelLabel = 'Упражнения';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('program_id')
                            ->label('Программа')
                            ->options(WorkoutProgramV2::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('instructions')
                            ->label('Инструкции')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Характеристики')
                    ->schema([
                        Forms\Components\TextInput::make('sets')
                            ->label('Количество подходов')
                            ->numeric()
                            ->minValue(1)
                            ->default(3)
                            ->step(1),
                        
                        Forms\Components\TextInput::make('reps')
                            ->label('Количество повторений')
                            ->numeric()
                            ->minValue(1)
                            ->default(10)
                            ->step(1),
                        
                        Forms\Components\TextInput::make('duration_seconds')
                            ->label('Длительность (секунды)')
                            ->numeric()
                            ->minValue(0)
                            ->step(5)
                            ->suffix('сек'),
                        
                        Forms\Components\TextInput::make('rest_seconds')
                            ->label('Отдых между подходами (секунды)')
                            ->numeric()
                            ->minValue(0)
                            ->default(60)
                            ->step(5)
                            ->suffix('сек'),
                        
                        Forms\Components\TextInput::make('weight_kg')
                            ->label('Вес (кг)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->suffix('кг'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\KeyValue::make('equipment_needed')
                            ->label('Необходимое оборудование')
                            ->keyLabel('Оборудование')
                            ->valueLabel('Описание')
                            ->addActionLabel('Добавить оборудование')
                            ->columnSpanFull(),
                        
                        Forms\Components\KeyValue::make('muscle_groups')
                            ->label('Группы мышц')
                            ->keyLabel('Группа мышц')
                            ->valueLabel('Приоритет')
                            ->addActionLabel('Добавить группу мышц')
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активно')
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
                Tables\Columns\TextColumn::make('program.name')
                    ->label('Программа')
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
                
                Tables\Columns\TextColumn::make('sets')
                    ->label('Подходы')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('reps')
                    ->label('Повторения')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Длительность')
                    ->numeric()
                    ->sortable()
                    ->suffix(' сек')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('rest_seconds')
                    ->label('Отдых')
                    ->numeric()
                    ->sortable()
                    ->suffix(' сек')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Вес')
                    ->numeric()
                    ->sortable()
                    ->suffix(' кг')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Статус')
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
                Tables\Filters\SelectFilter::make('program_id')
                    ->label('Программа')
                    ->options(WorkoutProgramV2::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                
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
            'index' => Pages\ListWorkoutExerciseV2S::route('/'),
            'create' => Pages\CreateWorkoutExerciseV2::route('/create'),
            'edit' => Pages\EditWorkoutExerciseV2::route('/{record}/edit'),
        ];
    }
}
