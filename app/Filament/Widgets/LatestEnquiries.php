<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Models\Enquiry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestEnquiries extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest enquiries';

    public function table(Table $table): Table
    {
        return $table
            ->query(Enquiry::query()->where('status', '!=', 'spam')->latest()->limit(8))
            ->paginated(false)
            ->recordUrl(fn (Enquiry $r) => EnquiryResource::getUrl('edit', ['record' => $r]))
            ->columns([
                TextColumn::make('created_at')->label('Received')->since(),
                TextColumn::make('name'),
                TextColumn::make('phone'),
                TextColumn::make('course.name')->label('Course')->placeholder(fn ($record) => ucfirst((string) $record->interest) ?: '—'),
                TextColumn::make('audience')->badge(),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => Enquiry::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => $state === 'new' ? 'warning' : 'gray'),
            ]);
    }
}
