<?php

namespace App\Filament\Resources\Challenges\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ChallengeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('challenges')
                    ->disk('public')
                    ->visibility('public')
                    ->label('صورة التحدي'),
                TextInput::make('title')
                    ->label('عنوان التحدي')
                    ->required(),
                Textarea::make('description')
                    ->label('وصف التحدي')
                    ->columnSpanFull(),
                Select::make('badge_id')
                    ->label('شارة المكافأة')
                    ->relationship('badge', 'name')
                    ->required(),
                DatePicker::make('start_date')
                    ->label('تاريخ البدء')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->required(),
            ]);
    }
}
