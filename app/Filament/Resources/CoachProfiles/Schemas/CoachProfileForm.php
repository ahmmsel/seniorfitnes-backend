<?php

namespace App\Filament\Resources\CoachProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CoachProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('profile_status')
                    ->label('حالة الملف الشخصي')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'active' => 'نشط',
                        'pending' => 'قيد الانتظار',
                        'suspended' => 'معلق',
                        default => $state,
                    })
                    ->required()
                    ->default('pending'),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                TextInput::make('specialty')
                    ->label('التخصص')
                    ->required(),
                TextInput::make('years_of_experience')
                    ->label('سنوات الخبرة')
                    ->required()
                    ->numeric(),
                TextInput::make('nutrition_price')
                    ->label('سعر التغذية')
                    ->required()
                    ->numeric(),
                TextInput::make('workout_price')
                    ->label('سعر التمارين')
                    ->required()
                    ->numeric(),
                TextInput::make('full_package_price')
                    ->label('سعر الباقة الكاملة')
                    ->required()
                    ->numeric(),
            ]);
    }
}
