<?php

namespace App\Filament\Resources\TraineeProfiles\Pages;

use App\Filament\Resources\TraineeProfiles\TraineeProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTraineeProfile extends EditRecord
{
    protected static string $resource = TraineeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
