<?php

namespace App\Filament\Resources\CoachProfiles\Pages;

use App\Filament\Resources\CoachProfiles\CoachProfileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCoachProfile extends ViewRecord
{
    protected static string $resource = CoachProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
