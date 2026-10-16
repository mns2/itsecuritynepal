<?php

namespace App\Filament\Trainer\Resources\MyClasses\Pages;

use App\Filament\Pages\TakeAttendance;
use App\Filament\Trainer\Resources\MyClasses\MyClassResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewMyClass extends ViewRecord
{
    protected static string $resource = MyClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('attendance')->label('Take attendance')->icon('heroicon-o-clipboard-document-check')
                ->url(fn () => TakeAttendance::getUrl(['class' => $this->record->id])),
        ];
    }
}
