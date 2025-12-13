<?php

namespace App\Filament\Resources\Challenges\Schemas;

use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ChallengeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryImageEntry::make('image')
                    ->collection('challenges')
                    ->label('صورة التحدي'),
                TextEntry::make('title')
                    ->label('عنوان التحدي'),
                TextEntry::make('badge.name')
                    ->label('شارة المكافأة')
                    ->numeric(),
                TextEntry::make('description')
                    ->label('وصف التحدي'),
                TextEntry::make('duration_days')
                    ->label('مدة التحدي (بالأيام)')
                    ->numeric(),
                TextEntry::make('start_date')
                    ->label('تاريخ البدء')
                    ->date(),
                TextEntry::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date(),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime(),
            ]);
    }
}
