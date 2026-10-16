<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Filament\Resources\Subscribers\SubscriberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscriber extends CreateRecord
{
    protected static string $resource = SubscriberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + [
            'source' => 'admin',
            'consent_at' => now(),
            'confirmed_at' => ($data['status'] ?? null) === 'subscribed' ? now() : null,
        ];
    }
}
