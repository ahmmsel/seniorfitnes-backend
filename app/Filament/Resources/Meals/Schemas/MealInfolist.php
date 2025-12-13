<?php

namespace App\Filament\Resources\Meals\Schemas;

use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MealInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryImageEntry::make('image')
                    ->label('صورة الوجبة')
                    ->collection('meals'),
                TextEntry::make('name')
                    ->label('اسم الوجبة'),
                TextEntry::make('description')
                    ->html()
                    ->label('الوصف'),
                TextEntry::make('calories')
                    ->label('السعرات'),
                TextEntry::make('protein')
                    ->label('البروتين'),
                TextEntry::make('carbs')
                    ->label('الكاربهيدرات'),
                TextEntry::make('fats')
                    ->label('الدهون'),
                TextEntry::make('date')
                    ->label('موعد الوجبة')
                    ->dateTime(),
                TextEntry::make('type')
                    ->label('نوع الوجبة'),
                TextEntry::make('created_at')
                    ->label('تم الإنشاء في')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('تم التحديث في')
                    ->dateTime(),
            ]);
    }
}
