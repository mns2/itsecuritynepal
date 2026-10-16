<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnquiries extends ListRecords
{
    protected static string $resource = EnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add enquiry')->icon('heroicon-o-plus')
                ->tooltip('Walk-in, phone, WhatsApp or social-media enquiry'),
        ];
    }
}
