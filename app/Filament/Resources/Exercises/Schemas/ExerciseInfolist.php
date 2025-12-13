<?php

namespace App\Filament\Resources\Exercises\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExerciseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryImageEntry::make('image')
                    ->collection('exercises')
                    ->label('صورة التمرين'),
                TextEntry::make('name')
                    ->label('اسم التمرين'),
                TextEntry::make('instructions')
                    ->html()
                    ->label('التعليمات'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->label('تم الإنشاء في'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('تم التحديث في'),
            ]);
    }
}
