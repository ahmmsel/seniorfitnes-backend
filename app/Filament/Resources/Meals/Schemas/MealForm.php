<?php

namespace App\Filament\Resources\Meals\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label('صورة الوجبة')
                    ->collection('meals')
                    ->disk('public')
                    ->visibility('public')
                    ->required(),
                TextInput::make('name')
                    ->label('اسم الوجبة')
                    ->required(),
                TextInput::make('slug')
                    ->label('عنوان فرعي (يجيب ان يكون باللغة الانجليزية)')
                    ->required(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                TextInput::make('calories')
                    ->label('السعرات')
                    ->required(),
                TextInput::make('protein')
                    ->label('البروتين')
                    ->required(),
                TextInput::make('carbs')
                    ->label('الكاربهيدرات')
                    ->required(),
                TextInput::make('fats')
                    ->label('الدهون')
                    ->required(),
                DateTimePicker::make('date')
                    ->label('موعد الوجبة'),
                Select::make('type')->options([
                    'breakfast' => 'الفطور',
                    'lunch' =>   'الغداء',
                    'dinner' => 'العشاء',
                    'snack' => 'وجبة خفيفة',
                    'other' => 'أخرى',
                ])->required(),
            ]);
    }
}
