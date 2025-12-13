<?php

namespace App\Filament\Resources\TraineeProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TraineeProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('height')
                    ->label('الطول (سم)')
                    ->required()
                    ->numeric(),
                TextInput::make('weight')
                    ->label('الوزن (كجم)')
                    ->required()
                    ->numeric(),
                Select::make('goal')
                    ->label('الهدف')
                    ->options([
                        'lose_weight' => 'فقدان الوزن',
                        'build_muscle' => 'بناء العضلات',
                        'maintain_fitness' => 'الحفاظ على اللياقة',
                        'improve_cardio' => 'تحسين اللياقة القلبية',
                    ])
                    ->required(),
                Select::make('level')
                    ->label('المستوى')
                    ->options([
                        'sedentary' => 'غير نشيط',
                        'lightly_active' => 'نشاط خفيف',
                        'active' => 'نشاط متوسط',
                        'very_active' => 'نشاط عالي',
                    ])
                    ->required(),
                Select::make('body_type')
                    ->label('نوع الجسم')
                    ->options([
                        'underweight' => 'نقص الوزن',
                        'normal' => 'وزن طبيعي',
                        'overweight' => 'زيادة في الوزن',
                        'obese' => 'سمنة',
                    ])
                    ->required(),
            ]);
    }
}
