<?php

namespace App\Filament\Resources\Workouts\Pages;

use App\Filament\Resources\Workouts\WorkoutResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkout extends ViewRecord
{
    protected static string $resource = WorkoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
