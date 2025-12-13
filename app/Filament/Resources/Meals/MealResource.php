<?php

namespace App\Filament\Resources\Meals;

use App\Filament\Resources\Meals\Pages\CreateMeal;
use App\Filament\Resources\Meals\Pages\EditMeal;
use App\Filament\Resources\Meals\Pages\ListMeals;
use App\Filament\Resources\Meals\Pages\ViewMeal;
use App\Filament\Resources\Meals\Schemas\MealForm;
use App\Filament\Resources\Meals\Schemas\MealInfolist;
use App\Filament\Resources\Meals\Tables\MealsTable;
use App\Models\Meal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Meal';
    protected static string | null | UnitEnum $navigationGroup  = 'إدارة الوجبات';

    protected static ?string $pluralModelLabel  = 'الوجبات';
    protected static ?string $modelLabel  = 'وجبة';

    public static function form(Schema $schema): Schema
    {
        return MealForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MealInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MealsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeals::route('/'),
            'create' => CreateMeal::route('/create'),
            'view' => ViewMeal::route('/{record}'),
            'edit' => EditMeal::route('/{record}/edit'),
        ];
    }
}
