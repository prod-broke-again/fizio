<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserWorkoutProgressV2Resource\Pages;
use App\Filament\Resources\UserWorkoutProgressV2Resource\RelationManagers;
use App\Models\User;
use App\Models\UserWorkoutProgressV2;
use App\Models\WorkoutExerciseV2;
use App\Models\WorkoutProgramV2;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserWorkoutProgressV2Resource extends Resource
{
    protected static ?string $model = UserWorkoutProgressV2::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationGroup = 'Тренировки V2';
    
    protected static ?string $navigationLabel = 'Прогресс тренировок';
    
    protected static ?string $modelLabel = 'Прогресс тренировки';
    
    protected static ?string $pluralModelLabel = 'Прогресс тренировок';

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
                        
                        Forms\Components\Select::make('program_id')
                            ->label('Программа тренировок')
                            ->options(WorkoutProgramV2::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('exercise_id', null);
                            }),
                        
                        Forms\Components\Select::make('exercise_id')
                            ->label('Упражнение')
                            ->options(function (callable $get) {
                                $programId = $get('program_id');
                                if (!$programId) return [];
                                
                                return WorkoutExerciseV2::where('program_id', $programId)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => !$get('program_id')),
                        
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Дата и время выполнения')
                            ->required()
                            ->default(now())
                            ->seconds(false),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Результаты')
                    ->schema([
                        Forms\Components\TextInput::make('duration_seconds')
                            ->label('Длительность (секунды)')
                            ->numeric()
                            ->minValue(0)
                            ->step(5)
                            ->suffix('сек')
                            ->helperText('Время выполнения упражнения'),
                        
                        Forms\Components\TextInput::make('sets_completed')
                            ->label('Выполнено подходов')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Фактически выполнено подходов'),
                        
                        Forms\Components\TextInput::make('reps_completed')
                            ->label('Выполнено повторений')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Фактически выполнено повторений'),
                        
                        Forms\Components\TextInput::make('weight_used_kg')
                            ->label('Использованный вес (кг)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->suffix('кг'),
                        
                        Forms\Components\TextInput::make('calories_burned')
                            ->label('Сожжено калорий')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->suffix('ккал'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Заметки')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Личные заметки о тренировке'),
                        
                        Forms\Components\Toggle::make('is_completed')
                            ->label('Завершено')
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
                
                Tables\Columns\TextColumn::make('program.name')
                    ->label('Программа')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('exercise.name')
                    ->label('Упражнение')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Выполнено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Длительность')
                    ->numeric()
                    ->sortable()
                    ->suffix(' сек')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('sets_completed')
                    ->label('Подходы')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('reps_completed')
                    ->label('Повторения')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('weight_used_kg')
                    ->label('Вес')
                    ->numeric()
                    ->sortable()
                    ->suffix(' кг')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('calories_burned')
                    ->label('Калории')
                    ->numeric()
                    ->sortable()
                    ->suffix(' ккал')
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Статус')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                
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
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Пользователь')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('program_id')
                    ->label('Программа')
                    ->options(WorkoutProgramV2::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('Только завершенные')
                    ->default(true),
                
                Tables\Filters\Filter::make('completed_today')
                    ->label('Выполнено сегодня')
                    ->query(fn (Builder $query): Builder => $query->whereDate('completed_at', today())),
                
                Tables\Filters\Filter::make('completed_this_week')
                    ->label('Выполнено на этой неделе')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])),
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
            ->defaultSort('completed_at', 'desc');
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
            'index' => Pages\ListUserWorkoutProgressV2S::route('/'),
            'create' => Pages\CreateUserWorkoutProgressV2::route('/create'),
            'edit' => Pages\EditUserWorkoutProgressV2::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'program', 'exercise']);
    }
}
