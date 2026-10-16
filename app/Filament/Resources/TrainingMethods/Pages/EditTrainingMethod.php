<?php

namespace App\Filament\Resources\TrainingMethods\Pages;

use App\Filament\Resources\TrainingMethods\TrainingMethodResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingMethod extends EditRecord
{
    protected static string $resource = TrainingMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
