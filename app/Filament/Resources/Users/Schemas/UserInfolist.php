<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('الاسم الكامل'),
                TextEntry::make('email')
                    ->label('البريد الإلكتروني'),
                TextEntry::make('role')
                    ->label('الدور')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'admin' => 'مشرف',
                        'coach' => 'مدرب',
                        'trainee' => 'متدرب',
                        default => $state,
                    }),
                TextEntry::make('email_verified_at')
                    ->label('تم التحقق من البريد الإلكتروني')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->label('تم الإنشاء في')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('تم التحديث في')
                    ->dateTime(),
            ]);
    }
}
