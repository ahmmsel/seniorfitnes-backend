<?php

namespace App\Filament\Resources\TraineeProfiles\Pages;

use App\Filament\Resources\TraineeProfiles\TraineeProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTraineeProfiles extends ListRecords
{
    protected static string $resource = TraineeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
