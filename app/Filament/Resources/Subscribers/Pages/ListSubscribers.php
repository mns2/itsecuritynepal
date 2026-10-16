<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->action(fn () => response()->streamDownload(function () {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['email', 'name', 'status', 'source', 'signed_up', 'consent_at', 'confirmed_at']);
                    Subscriber::orderBy('id')->chunk(500, function ($rows) use ($out) {
                        foreach ($rows as $s) {
                            fputcsv($out, [$s->email, $s->name, $s->status, $s->source, $s->created_at?->toDateTimeString(), $s->consent_at?->toDateTimeString(), $s->confirmed_at?->toDateTimeString()]);
                        }
                    });
                    fclose($out);
                }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv'])),
            CreateAction::make()->label('Add subscriber'),
        ];
    }
}
