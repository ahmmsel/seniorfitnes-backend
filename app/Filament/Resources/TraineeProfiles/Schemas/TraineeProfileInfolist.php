<?php

namespace App\Filament\Resources\TraineeProfiles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TraineeProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('المستخدم')
                    ->numeric(),
                TextEntry::make('height')
                    ->label('الطول (سم)')
                    ->numeric(),
                TextEntry::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric(),
                TextEntry::make('goal')
                    ->label('الهدف')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'lose_weight' => 'فقدان الوزن',
                        'build_muscle' => 'بناء العضلات',
                        'maintain_fitness' => 'الحفاظ على اللياقة',
                        'improve_cardio' => 'تحسين اللياقة القلبية',
                        default => $state,
                    }),
                TextEntry::make('level')
                    ->label('المستوى')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'sedentary' => 'غير نشيط',
                        'lightly_active' => 'نشاط خفيف',
                        'active' => 'نشاط متوسط',
                        'very_active' => 'نشاط عالي',
                        default => $state,
                    }),
                TextEntry::make('body_type')
                    ->label('نوع الجسم')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'underweight' => 'نقص الوزن',
                        'normal' => 'وزن طبيعي',
                        'overweight' => 'زيادة في الوزن',
                        'obese' => 'سمنة',
                        default => $state,
                    }),
                TextEntry::make('created_at')
                    ->label('تم الإنشاء في')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('تم التحديث في')
                    ->dateTime(),
            ]);
    }
}
