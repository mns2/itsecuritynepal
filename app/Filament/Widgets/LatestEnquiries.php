<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEnquiries extends TableWidget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Current leads';

    public function table(Table $table): Table
    {
        return $table
            ->query(Enquiry::query()->whereIn('status', ['new', 'contacted'])->with('course')->orderByRaw("case when status = 'new' then 0 else 1 end")->latest()->limit(8))
            ->emptyStateHeading('No open leads')->emptyStateDescription('New website enquiries appear here until they are marked Enrolled or Closed.')
            ->paginated(false)
            ->recordUrl(fn (Enquiry $r) => EnquiryResource::getUrl('edit', ['record' => $r]))
            ->columns([
                TextColumn::make('created_at')->label('Received')->since(),
                TextColumn::make('name'),
                TextColumn::make('phone'),
                TextColumn::make('course.name')->label('Course')->placeholder(fn ($record) => ucfirst((string) $record->interest) ?: '—'),
                TextColumn::make('audience')->badge(),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => Enquiry::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => $state === 'new' ? 'warning' : 'info'),
            ]);
    }
}
