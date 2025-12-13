<?php

namespace App\Filament\Resources\CoachProfiles\Pages;

use App\Filament\Resources\CoachProfiles\CoachProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCoachProfile extends EditRecord
{
    protected static string $resource = CoachProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
