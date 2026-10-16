<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Models\Payment;
use App\Models\Student;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Payment history (instalments) for one student, with total collected and balance. */
class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('amount')->numeric()->minValue(1)->prefix('Rs')->required()
                    ->default(fn () => $this->getOwnerRecord()->feeBalance() ?: null)
                    ->helperText(fn () => ($b = $this->getOwnerRecord()->feeBalance()) ? 'Balance before this payment: Rs '.number_format($b) : null),
                DatePicker::make('paid_on')->label('Paid on')->required()->native(false)->default(now())->maxDate(now()),
                Select::make('method')->label('Paid by')->options(Student::PAYMENT_METHODS)->default('cash')->required()->native(false),
                TextInput::make('receipt_no')->label('Receipt / bill no.')->maxLength(60),
            ]),
            TextInput::make('note')->maxLength(200)->placeholder('e.g. 2nd instalment'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_on', 'desc')
            ->description(function () {
                /** @var Student $s */
                $s = $this->getOwnerRecord()->refresh();
                if ($s->feePayable() === null) {
                    return 'No course fee recorded yet: set it in the Fee section above.';
                }

                return 'Payable Rs '.number_format($s->feePayable()).' · Paid Rs '.number_format((int) $s->fee_paid).' · Balance Rs '.number_format((int) $s->feeBalance())
                    .($s->fee_due_on && $s->feeBalance() ? ' (due '.$s->fee_due_on->format('j M Y').')' : '');
            })
            ->columns([
                TextColumn::make('paid_on')->label('Date')->date('j M Y')->sortable(),
                TextColumn::make('amount')->money('NPR', locale: 'en_NP')->alignEnd()
                    ->summarize(Sum::make()->label('Total')->money('NPR', locale: 'en_NP')),
                TextColumn::make('method')->label('Paid by')->formatStateUsing(fn ($state) => Student::PAYMENT_METHODS[$state] ?? $state),
                TextColumn::make('receipt_no')->label('Receipt')->placeholder('—'),
                TextColumn::make('note')->placeholder('—')->toggleable(),
                TextColumn::make('receivedBy.name')->label('Received by')->placeholder('—')->toggleable(),
            ])
            ->headerActions([CreateAction::make()->label('Record payment')->icon('heroicon-o-banknotes')])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('No payments recorded');
    }
}
