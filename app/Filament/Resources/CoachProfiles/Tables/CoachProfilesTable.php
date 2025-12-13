<?php

namespace App\Filament\Resources\CoachProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoachProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profile_status')
                    ->label('حالة الملف الشخصي')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'approved' => 'موافق عليه',
                        'rejected' => 'مرفوض',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('specialty')
                    ->label('التخصص')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'nutrition' => 'تغذية',
                        'workout' => 'تمارين',
                        'both' => 'كليهما',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('years_of_experience')
                    ->label('سنوات الخبرا')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nutrition_price')
                    ->label('سعر التغذية')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('workout_price')
                    ->label('سعر التمارين')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('full_package_price')
                    ->label('سعر الباقة الكاملة')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profile_status')
                    ->label('حالة الملف الشخصي')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'active' => 'نشط',
                            'pending' => 'قيد الانتظار',
                            'suspended' => 'معلق',
                            default => $state,
                        };
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
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
