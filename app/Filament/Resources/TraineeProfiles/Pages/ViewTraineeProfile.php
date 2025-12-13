<?php

namespace App\Filament\Resources\TraineeProfiles\Pages;

use App\Filament\Resources\TraineeProfiles\TraineeProfileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTraineeProfile extends ViewRecord
{
    protected static string $resource = TraineeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
