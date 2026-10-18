<?php

namespace App\Filament\Resources\ExamBookings\Pages;

use App\Filament\Resources\ExamBookings\ExamBookingResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListExamBookings extends ListRecords
{
    protected static string $resource = ExamBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('page')->label('View booking page')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                ->url('/red-hat-exam-booking-nepal/')->openUrlInNewTab(),
        ];
    }
}
