<?php

namespace App\Filament\Resources\Badges\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BadgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('badges')
                    ->disk('public')
                    ->visibility('public')
                    ->label('صورة الشارة'),
                TextInput::make('name')
                    ->label('اسم الشارة')
                    ->required(),
                Textarea::make('description')
                    ->label('وصف الشارة')
                    ->columnSpanFull(),
            ]);
    }
}
