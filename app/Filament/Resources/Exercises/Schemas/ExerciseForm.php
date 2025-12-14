<?php

namespace App\Filament\Resources\Exercises\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExerciseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label('صورة التمرين')
                    ->collection('exercises')
                    ->disk('public')
                    ->visibility('public')
                    ->required(),
                TextInput::make('name')
                    ->label('اسم التمرين')
                    ->columnSpanFull()
                    ->required(),
                Textarea::make('instructions')
                    ->label('التعليمات')
                    ->columnSpanFull()
            ]);
    }
}
