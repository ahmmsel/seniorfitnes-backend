<?php

namespace App\Filament\Resources\Workouts\RelationManagers;

use App\Filament\Resources\Exercises\Schemas\ExerciseForm;
use App\Filament\Resources\Exercises\Schemas\ExerciseInfolist;
use App\Filament\Resources\Exercises\Tables\ExercisesTable;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ExercisesRelationManager extends RelationManager
{
    protected static string $relationship = 'exercises';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(ExerciseForm::configure($schema)->getComponents());
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(ExerciseInfolist::configure($schema)->getComponents());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(ExercisesTable::configure($table)->getColumns())
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make()->preloadRecordSelect()->schema(fn(AttachAction $action): array  => [
                    $action->getRecordSelect()->label('اختر التمرين'),
                    TextInput::make('sets')
                        ->label('المجموعات')
                        ->numeric(),
                    TextInput::make('reps')
                        ->label('التكرارات')
                        ->numeric(),
                ])
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DetachAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
