<?php

namespace App\Filament\Resources\Badges\Schemas;

use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BadgeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryImageEntry::make('image')
                    ->collection('badges')
                    ->label('صورة الشارة'),
                TextEntry::make('name')
                    ->label('اسم الشارة'),
                TextEntry::make('description')
                    ->label('وصف الشارة'),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime(),
            ]);
    }
}
