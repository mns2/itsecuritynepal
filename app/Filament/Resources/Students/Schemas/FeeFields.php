<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

/** Fee agreement + payment fields, shared by the Students form and "Enrol as student" on enquiries. */
class FeeFields
{
    /** $requireConfirmation = the enrol form: "Paid now" becomes the first payment record. */
    public static function section(bool $requireConfirmation = false): Section
    {
        $money = fn (string $name, string $label) => TextInput::make($name)->label($label)->numeric()->minValue(0)->prefix('Rs')->live(onBlur: true);

        return Section::make('Fee')->columnSpanFull()->compact()->schema([
            Grid::make(4)->schema([
                $money('fee_total', 'Course fee')->required($requireConfirmation),
                $money('fee_discount', 'Discount')->default(0),
                TextInput::make('discount_reason')->label('Discount reason')->maxLength(120)->placeholder('e.g. Dashain offer, group')->columnSpan(2),
                $money('fee_paid', $requireConfirmation ? 'Paid now' : 'Paid so far')->default(0)
                    ->disabled(! $requireConfirmation)->dehydrated($requireConfirmation)
                    ->helperText($requireConfirmation ? null : 'Sum of the payments below.'),
                Select::make('payment_method')->label('Paid by')->options(Student::PAYMENT_METHODS)->native(false)->visible($requireConfirmation)
                    ->required(fn (Get $get) => (int) $get('fee_paid') > 0),
                TextInput::make('receipt_no')->label('Receipt / bill no.')->maxLength(60)->visible($requireConfirmation),
                DatePicker::make('fee_due_on')->label('Balance due on')->native(false)
                    ->visible(fn (Get $get) => self::balance($get) > 0),
            ]),
            Placeholder::make('fee_summary')->hiddenLabel()->content(function (Get $get) {
                if (blank($get('fee_total'))) {
                    return 'Enter the course fee to see the balance.';
                }
                $payable = max(0, (int) $get('fee_total') - (int) $get('fee_discount'));

                return 'Payable: Rs '.number_format($payable).'   ·   Paid: Rs '.number_format((int) $get('fee_paid')).'   ·   Balance: Rs '.number_format(self::balance($get));
            }),
            Toggle::make('fee_confirmed')->label('Fee and payment plan confirmed with the student')
                ->accepted($requireConfirmation)
                ->helperText($requireConfirmation ? 'Required to enrol.' : 'Records who confirmed it and when.'),
        ]);
    }

    private static function balance(Get $get): int
    {
        if (blank($get('fee_total'))) {
            return 0;
        }

        return max(0, (int) $get('fee_total') - (int) $get('fee_discount') - (int) $get('fee_paid'));
    }

    /** Stamp who confirmed the fee (call before saving). */
    public static function stampConfirmation(array $data, ?Student $existing = null): array
    {
        if (! empty($data['fee_confirmed']) && ! $existing?->fee_confirmed) {
            $data['fee_confirmed_by'] = auth()->id();
            $data['fee_confirmed_at'] = now();
        }
        if (array_key_exists('fee_confirmed', $data) && empty($data['fee_confirmed'])) {
            $data['fee_confirmed_by'] = null;
            $data['fee_confirmed_at'] = null;
        }

        return $data;
    }
}
