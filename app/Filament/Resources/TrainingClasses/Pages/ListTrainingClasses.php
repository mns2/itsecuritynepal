<?php

namespace App\Filament\Resources\TrainingClasses\Pages;

use App\Filament\Resources\TrainingClasses\TrainingClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingClasses extends ListRecords
{
    protected static string $resource = TrainingClassResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
