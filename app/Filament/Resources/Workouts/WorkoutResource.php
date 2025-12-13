<?php

namespace App\Filament\Resources\Workouts;

use App\Filament\Resources\Workouts\Pages\CreateWorkout;
use App\Filament\Resources\Workouts\Pages\EditWorkout;
use App\Filament\Resources\Workouts\Pages\ListWorkouts;
use App\Filament\Resources\Workouts\Pages\ViewWorkout;
use App\Filament\Resources\Workouts\RelationManagers\ExercisesRelationManager;
use App\Filament\Resources\Workouts\Schemas\WorkoutForm;
use App\Filament\Resources\Workouts\Schemas\WorkoutInfolist;
use App\Filament\Resources\Workouts\Tables\WorkoutsTable;
use App\Models\Workout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WorkoutResource extends Resource
{
    protected static ?string $model = Workout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Workout';
    protected static string | null | UnitEnum $navigationGroup  = 'إدارة التمارين';

    protected static ?string $pluralModelLabel  = 'التمارين الرياضية';
    protected static ?string $modelLabel  = 'تمرين رياضي';


    public static function form(Schema $schema): Schema
    {
        return WorkoutForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WorkoutInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkoutsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ExercisesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkouts::route('/'),
            'create' => CreateWorkout::route('/create'),
            'view' => ViewWorkout::route('/{record}'),
            'edit' => EditWorkout::route('/{record}/edit'),
        ];
    }
}
