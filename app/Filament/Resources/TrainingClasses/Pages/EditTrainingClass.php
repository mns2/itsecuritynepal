<?php

namespace App\Filament\Resources\TrainingClasses\Pages;

use App\Filament\Resources\TrainingClasses\TrainingClassResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingClass extends EditRecord
{
    protected static string $resource = TrainingClassResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
