<?php

namespace App\Filament\Resources\CoachProfiles;

use App\Filament\Resources\CoachProfiles\Pages\CreateCoachProfile;
use App\Filament\Resources\CoachProfiles\Pages\EditCoachProfile;
use App\Filament\Resources\CoachProfiles\Pages\ListCoachProfiles;
use App\Filament\Resources\CoachProfiles\Pages\ViewCoachProfile;
use App\Filament\Resources\CoachProfiles\Schemas\CoachProfileForm;
use App\Filament\Resources\CoachProfiles\Schemas\CoachProfileInfolist;
use App\Filament\Resources\CoachProfiles\Tables\CoachProfilesTable;
use App\Models\CoachProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CoachProfileResource extends Resource
{
    protected static ?string $model = CoachProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'المستخدمون والإدارة';

    protected static ?string $recordTitleAttribute = 'CoachProfile';

    protected static ?string $pluralModelLabel  = 'ملفات المدربين';
    protected static ?string $modelLabel  = 'ملف المدرب';

    public static function form(Schema $schema): Schema
    {
        return CoachProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CoachProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoachProfilesTable::configure($table);
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
            'index' => ListCoachProfiles::route('/'),
            'create' => CreateCoachProfile::route('/create'),
            'view' => ViewCoachProfile::route('/{record}'),
            'edit' => EditCoachProfile::route('/{record}/edit'),
        ];
    }
}
