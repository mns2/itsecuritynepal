<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Students\StudentResource;
use App\Models\Payment;
use App\Models\Student;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Fee collection: every payment received, with totals for any date range, method, course or batch. */
class PaymentResource extends Resource
{
    use \App\Filament\Concerns\RestrictedToArea;

    protected static string $accessArea = 'finance';

    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Fee collection';

    protected static ?string $modelLabel = 'payment';

    public static function canCreate(): bool
    {
        return false; // payments are recorded on the student (Students → student → Payments)
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_on', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['student.course', 'student.trainingClass', 'receivedBy']))
            ->columns([
                TextColumn::make('paid_on')->label('Date')->date('j M Y')->sortable(),
                TextColumn::make('student.name')->label('Student')->searchable()
                    ->url(fn (Payment $r) => $r->student ? StudentResource::getUrl('edit', ['record' => $r->student]) : null)
                    ->description(fn (Payment $r) => $r->student?->course?->code.($r->student?->trainingClass ? ' · '.$r->student->trainingClass->name : '')),
                TextColumn::make('amount')->money('NPR', locale: 'en_NP')->alignEnd()->sortable()
                    ->summarize([Sum::make()->label('Collected')->money('NPR', locale: 'en_NP'), Count::make()->label('Payments')]),
                TextColumn::make('method')->label('Paid by')->badge()->color('gray')->formatStateUsing(fn ($state) => Student::PAYMENT_METHODS[$state] ?? $state),
                TextColumn::make('receipt_no')->label('Receipt')->placeholder('—')->searchable(),
                TextColumn::make('receivedBy.name')->label('Received by')->placeholder('—')->toggleable(),
            ])
            ->groups([
                Group::make('paid_on')->label('Day')->date()->collapsible(),
                Group::make('method')->label('Payment method')->getTitleFromRecordUsing(fn (Payment $r) => Student::PAYMENT_METHODS[$r->method] ?? $r->method),
                Group::make('student.course.name')->label('Course'),
                Group::make('student.trainingClass.name')->label('Batch / class'),
            ])
            ->filters([
                Filter::make('period')->schema([
                    DatePicker::make('from')->label('From')->native(false),
                    DatePicker::make('until')->label('Until')->native(false),
                ])->columns(2)
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('paid_on', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('paid_on', '<=', $d)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        ($data['from'] ?? null) ? 'From '.\Illuminate\Support\Carbon::parse($data['from'])->format('j M Y') : null,
                        ($data['until'] ?? null) ? 'Until '.\Illuminate\Support\Carbon::parse($data['until'])->format('j M Y') : null,
                    ])),
                SelectFilter::make('method')->label('Paid by')->options(Student::PAYMENT_METHODS)->multiple(),
                SelectFilter::make('course')->label('Course')->relationship('student.course', 'name')->searchable()->preload(),
                SelectFilter::make('batch')->label('Batch / class')->relationship('student.trainingClass', 'name')->searchable()->preload(),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->recordUrl(fn (Payment $r) => $r->student ? StudentResource::getUrl('edit', ['record' => $r->student]) : null)
            ->emptyStateHeading('No payments yet')
            ->emptyStateDescription('Payments are recorded on each student (Students → open a student → Payments), or when enrolling from an enquiry.');
    }

    public static function getPages(): array
    {
        return ['index' => ListPayments::route('/')];
    }
}
