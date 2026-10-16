<?php

namespace App\Filament\Resources\TrainingMethods\Pages;

use App\Filament\Resources\TrainingMethods\TrainingMethodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingMethods extends ListRecords
{
    protected static string $resource = TrainingMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
