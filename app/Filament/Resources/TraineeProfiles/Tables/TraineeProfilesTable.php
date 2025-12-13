<?php

namespace App\Filament\Resources\TraineeProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TraineeProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('height')
                    ->label('الطول (سم)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('goal')
                    ->label('الهدف')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'lose_weight' => 'فقدان الوزن',
                        'build_muscle' => 'بناء العضلات',
                        'maintain_fitness' => 'الحفاظ على اللياقة',
                        'improve_cardio' => 'تحسين اللياقة القلبية',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('level')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'sedentary' => 'غير نشيط',
                        'lightly_active' => 'نشاط خفيف',
                        'active' => 'نشاط متوسط',
                        'very_active' => 'نشاط عالي',
                        default => $state,
                    })
                    ->label('المستوى')
                    ->searchable(),
                TextColumn::make('body_type')
                    ->label('نوع الجسم')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'underweight' => 'نقص الوزن',
                        'normal' => 'وزن طبيعي',
                        'overweight' => 'زيادة في الوزن',
                        'obese' => 'سمنة',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('تم الإنشاء في')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تم التحديث في')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
