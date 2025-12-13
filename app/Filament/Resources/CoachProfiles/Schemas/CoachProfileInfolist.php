<?php

namespace App\Filament\Resources\CoachProfiles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CoachProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('المستخدم')
                    ->numeric(),
                TextEntry::make('profile_status')
                    ->label('حالة الملف الشخصي')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'active' => 'نشط',
                        'pending' => 'قيد الانتظار',
                        'suspended' => 'معلق',
                        default => $state,
                    }),
                TextEntry::make('specialty')
                    ->label('التخصص'),
                TextEntry::make('years_of_experience')
                    ->label('سنوات الخبرة')
                    ->numeric(),
                TextEntry::make('nutrition_price')
                    ->label('سعر التغذية')
                    ->numeric(),
                TextEntry::make('workout_price')
                    ->label('سعر التمارين')
                    ->numeric(),
                TextEntry::make('full_package_price')
                    ->label('سعر الباقة الكاملة')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime(),
            ]);
    }
}
