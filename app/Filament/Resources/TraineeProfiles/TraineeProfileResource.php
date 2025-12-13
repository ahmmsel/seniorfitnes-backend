<?php

namespace App\Filament\Resources\TraineeProfiles;

use App\Filament\Resources\TraineeProfiles\Pages\CreateTraineeProfile;
use App\Filament\Resources\TraineeProfiles\Pages\EditTraineeProfile;
use App\Filament\Resources\TraineeProfiles\Pages\ListTraineeProfiles;
use App\Filament\Resources\TraineeProfiles\Pages\ViewTraineeProfile;
use App\Filament\Resources\TraineeProfiles\Schemas\TraineeProfileForm;
use App\Filament\Resources\TraineeProfiles\Schemas\TraineeProfileInfolist;
use App\Filament\Resources\TraineeProfiles\Tables\TraineeProfilesTable;
use App\Models\TraineeProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TraineeProfileResource extends Resource
{
    protected static ?string $model = TraineeProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'المستخدمون والإدارة';

    protected static ?string $recordTitleAttribute = 'TraineeProfile';

    protected static ?string $pluralModelLabel  = 'ملفات المتدربين';
    protected static ?string $modelLabel  = 'ملف المتدرب';

    public static function form(Schema $schema): Schema
    {
        return TraineeProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TraineeProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TraineeProfilesTable::configure($table);
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
            'index' => ListTraineeProfiles::route('/'),
            'create' => CreateTraineeProfile::route('/create'),
            'view' => ViewTraineeProfile::route('/{record}'),
            'edit' => EditTraineeProfile::route('/{record}/edit'),
        ];
    }
}
