<?php

namespace App\Filament\Resources\ExamBookings\Schemas;

use App\Models\ExamBooking;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** What the candidate sent is read-only; staff change only status, exam date and notes. */
class ExamBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Section::make('Booking request')->columnSpan(1)->schema([
                Grid::make(2)->schema([
                    TextEntry::make('reference')->fontFamily('mono')->weight('bold')->copyable(),
                    TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i a'),
                    TextEntry::make('exam')->columnSpanFull(),
                    TextEntry::make('fee_npr')->label('Fee shown to candidate')->formatStateUsing(fn ($state) => 'NPR '.number_format((int) $state)),
                    TextEntry::make('transaction_id')->label('Transaction ID')->placeholder('Not given')->copyable(),
                    TextEntry::make('name'),
                    TextEntry::make('phone')->url(fn ($record) => 'tel:'.preg_replace('/[^0-9+]/', '', $record->phone))->copyable(),
                    TextEntry::make('email')->url(fn ($record) => 'mailto:'.$record->email)->copyable()->columnSpanFull(),
                    TextEntry::make('redhat_login')->label('Red Hat login')->copyable(),
                    TextEntry::make('redhat_email')->label('Red Hat account email')->copyable(),
                ]),
            ]),
            Section::make('Payment proof')->columnSpan(1)
                ->description('Match the amount, date and transaction ID with the statement before verifying.')
                ->schema([
                    ViewEntry::make('proof_path')->hiddenLabel()->view('filament.exam-booking-proof'),
                ]),
            Section::make('Review')->columnSpanFull()->schema([
                Grid::make(2)->schema([
                    Select::make('status')->options(ExamBooking::STATUSES)->required()->native(false)
                        ->helperText('Prefer the buttons at the top: they also email the candidate. Changing this field does not.'),
                    DateTimePicker::make('exam_at')->label('Exam date and time (once booked)')->native(false)->seconds(false),
                ]),
                Textarea::make('staff_notes')->rows(4)->placeholder('Payment checked against statement, exam booked for…, reason if not confirmed…'),
                TextEntry::make('reviewer.name')->label('Last status change')
                    ->formatStateUsing(fn ($state, $record) => $state.($record->reviewed_at ? ' · '.$record->reviewed_at->format('j M Y, g:i a') : ''))
                    ->placeholder('Not reviewed yet'),
            ]),
        ]);
    }
}
