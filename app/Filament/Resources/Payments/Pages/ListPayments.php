<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Widgets\CollectionStats;
use App\Filament\Widgets\CollectionByMethod;
use App\Filament\Widgets\CollectionByMonth;
use App\Models\Payment;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderWidgets(): array
    {
        return [CollectionStats::class, CollectionByMonth::class, CollectionByMethod::class];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->action(fn () => response()->streamDownload(function () {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['date', 'student', 'phone', 'course', 'batch', 'amount_npr', 'method', 'receipt', 'note', 'received_by']);
                    Payment::with(['student.course', 'student.trainingClass', 'receivedBy'])->orderBy('paid_on')->chunk(500, function ($rows) use ($out) {
                        foreach ($rows as $p) {
                            fputcsv($out, [$p->paid_on->toDateString(), $p->student?->name, $p->student?->phone, $p->student?->course?->name, $p->student?->trainingClass?->name,
                                $p->amount, Student::PAYMENT_METHODS[$p->method] ?? $p->method, $p->receipt_no, $p->note, $p->receivedBy?->name]);
                        }
                    });
                    fclose($out);
                }, 'fee-collection-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv'])),
        ];
    }
}
