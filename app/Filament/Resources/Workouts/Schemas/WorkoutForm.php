<?php

namespace App\Filament\Resources\Workouts\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WorkoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label('صورة البرنامج التدريبي')
                    ->collection('workouts')
                    ->disk('public')
                    ->visibility('public')
                    ->required(),
                TextInput::make('name')
                    ->label('اسم البرنامج التدريبي')
                    ->columnSpanFull()
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
