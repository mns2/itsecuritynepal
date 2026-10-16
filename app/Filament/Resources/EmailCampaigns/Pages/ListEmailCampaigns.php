<?php

namespace App\Filament\Resources\EmailCampaigns\Pages;

use App\Filament\Resources\EmailCampaigns\EmailCampaignResource;
use App\Filament\Widgets\NewsletterSending;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailCampaigns extends ListRecords
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderWidgets(): array
    {
        return [NewsletterSending::class];
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New email')];
    }
}
