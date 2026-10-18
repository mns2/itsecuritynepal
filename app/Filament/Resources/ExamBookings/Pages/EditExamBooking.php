<?php

namespace App\Filament\Resources\ExamBookings\Pages;

use App\Filament\Resources\ExamBookings\ExamBookingResource;
use App\Models\ExamBooking;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditExamBooking extends EditRecord
{
    protected static string $resource = ExamBookingResource::class;

    public function getTitle(): string
    {
        return 'Exam booking '.$this->getRecord()->reference;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')->label('Payment verified')->icon('heroicon-o-banknotes')->color('info')
                ->visible(fn (ExamBooking $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalDescription('Confirm you found this payment in the account statement (amount, date and transaction ID match).')
                ->action(fn (ExamBooking $record) => $this->setStatus($record, 'verified')),
            Action::make('book')->label('Exam booked')->icon('heroicon-o-calendar-days')->color('success')
                ->visible(fn (ExamBooking $record) => in_array($record->status, ['pending', 'verified'], true))
                ->schema([
                    DateTimePicker::make('exam_at')->label('Exam date and time')->native(false)->seconds(false),
                    Textarea::make('note')->label('Note (optional)')->rows(2),
                ])
                ->action(fn (ExamBooking $record, array $data) => $this->setStatus($record, 'booked', $data['note'] ?? null, $data['exam_at'] ?? null)),
            Action::make('reject')->label('Payment not confirmed')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn (ExamBooking $record) => in_array($record->status, ['pending', 'verified'], true))
                ->schema([Textarea::make('note')->label('Reason (kept in staff notes)')->required()->rows(2)])
                ->action(fn (ExamBooking $record, array $data) => $this->setStatus($record, 'rejected', $data['note'])),
            DeleteAction::make()->modalDescription('Deletes the booking and its payment screenshot permanently.'),
        ];
    }

    private function setStatus(ExamBooking $record, string $status, ?string $note = null, ?string $examAt = null): void
    {
        $record->status = $status;
        if ($examAt) {
            $record->exam_at = $examAt;
        }
        if (filled($note)) {
            $line = now()->format('j M Y').' · '.ExamBooking::STATUSES[$status].': '.$note;
            $record->staff_notes = trim(($record->staff_notes ? $record->staff_notes."\n" : '').$line);
        }
        $record->save();

        $this->refreshFormData(['status', 'exam_at', 'staff_notes']);
        Notification::make()->success()->title(ExamBooking::STATUSES[$status])->send();
    }
}
