<?php

namespace App\Filament\Resources\ExamBookings;

use App\Filament\Resources\ExamBookings\Pages\EditExamBooking;
use App\Filament\Resources\ExamBookings\Pages\ListExamBookings;
use App\Filament\Resources\ExamBookings\Schemas\ExamBookingForm;
use App\Filament\Resources\ExamBookings\Tables\ExamBookingsTable;
use App\Models\ExamBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** Red Hat exam booking requests: staff check the payment screenshot, then mark verified / booked / not confirmed. */
class ExamBookingResource extends Resource
{
    use \App\Filament\Concerns\RestrictedToArea;

    protected static string $accessArea = 'finance';

    protected static ?string $model = ExamBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|\UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Exam bookings';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function canCreate(): bool
    {
        return false; // bookings come only from the website form (with a payment proof)
    }

    public static function form(Schema $schema): Schema
    {
        return ExamBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamBookingsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $n = ExamBooking::where('status', 'pending')->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamBookings::route('/'),
            'edit' => EditExamBooking::route('/{record}/edit'),
        ];
    }
}
