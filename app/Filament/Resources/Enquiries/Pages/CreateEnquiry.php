<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use Filament\Resources\Pages\CreateRecord;

/** Walk-in, phone or social-media enquiry typed in by staff. */
class CreateEnquiry extends CreateRecord
{
    protected static string $resource = EnquiryResource::class;

    protected static ?string $title = 'New enquiry';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + [
            'email' => $data['email'] ?? '',
            'consent' => true,              // given in person / on the phone
            'created_by' => auth()->id(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return EnquiryResource::getUrl('edit', ['record' => $this->record]);
    }
}
