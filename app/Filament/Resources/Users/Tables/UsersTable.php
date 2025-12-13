<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم الكامل')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('الدور')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'admin' => 'مشرف',
                        'coach' => 'مدرب',
                        'trainee' => 'متدرب',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label('تم التحقق من البريد الإلكتروني')
                    ->dateTime()
                    ->sortable(),
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
