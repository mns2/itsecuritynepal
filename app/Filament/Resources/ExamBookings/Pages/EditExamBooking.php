<?php

namespace App\Filament\Resources\ExamBookings\Pages;

use App\Filament\Resources\ExamBookings\ExamBookingResource;
use App\Http\Controllers\ExamBookingController;
use App\Mail\ExamBookingUpdate;
use App\Models\ExamBooking;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            // Candidate has not clicked the email link yet: resend it, or confirm it for them after a phone call.
            Action::make('resendConfirmation')->label('Resend confirmation email')->icon('heroicon-o-envelope')->color('gray')
                ->visible(fn (ExamBooking $record) => ! $record->emailVerified())
                ->action(function (ExamBooking $record) {
                    $ok = ExamBookingController::sendConfirmation($record);
                    $n = Notification::make()->title($ok ? 'Confirmation email sent to '.$record->email : 'Email could not be sent (check the mail settings)');
                    ($ok ? $n->success() : $n->danger())->send();
                }),
            Action::make('confirmEmail')->label('Mark email confirmed')->icon('heroicon-o-check-badge')->color('gray')
                ->visible(fn (ExamBooking $record) => ! $record->emailVerified())
                ->requiresConfirmation()
                ->modalDescription('Only if you confirmed the email address with the candidate yourself (e.g. by phone). The booking then moves to Pending verification.')
                ->action(function (ExamBooking $record) {
                    ExamBookingController::markEmailVerified($record);
                    $this->refreshFormData(['status']);
                    Notification::make()->success()->title('Email marked as confirmed')->send();
                }),
            Action::make('verify')->label('Payment verified')->icon('heroicon-o-banknotes')->color('info')
                ->visible(fn (ExamBooking $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalDescription('Confirm you found this payment in the account statement (amount, date and transaction ID match). The candidate is emailed that payment is verified and exam booking is in progress.')
                ->action(fn (ExamBooking $record) => $this->setStatus($record, 'verified')),
            Action::make('book')->label('Exam booked')->icon('heroicon-o-calendar-days')->color('success')
                ->visible(fn (ExamBooking $record) => $record->status === 'verified')
                ->modalDescription('Once the exam is booked with Red Hat. The candidate is emailed the confirmation.')
                ->schema([
                    DateTimePicker::make('exam_at')->label('Exam date and time')->native(false)->seconds(false)->helperText('Shown in the confirmation email and on the tracking page.'),
                    Textarea::make('note')->label('Staff note (optional, not sent)')->rows(2),
                ])
                ->action(fn (ExamBooking $record, array $data) => $this->setStatus($record, 'booked', $data['note'] ?? null, $data['exam_at'] ?? null)),
            Action::make('reject')->label('Payment not confirmed')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn (ExamBooking $record) => in_array($record->status, ['pending', 'verified'], true))
                ->modalDescription('The candidate is emailed to call you with their payment details. The reason is kept in staff notes only.')
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

        // Tell the candidate (payment verified / exam booked / not confirmed). Staff see it if the email failed.
        if (in_array($status, ['verified', 'booked', 'rejected'], true)) {
            try {
                Mail::to($record->email, $record->name)->send(new ExamBookingUpdate($record));
                Notification::make()->success()->title('Candidate emailed at '.$record->email)->send();
            } catch (\Throwable $e) {
                Log::error('Exam booking update email failed', ['booking' => $record->id, 'error' => $e->getMessage()]);
                Notification::make()->danger()->persistent()->title('Email to the candidate failed')
                    ->body('Status saved, but please inform '.$record->name.' by phone ('.$record->phone.').')->send();
            }
        }
    }
}
