<?php

namespace App\Filament\Resources\ExamBookings\Tables;

use App\Models\ExamBooking;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamBookingsTable
{
    public static function statusColor(?string $state): string
    {
        return match ($state) {
            'unverified' => 'gray', 'pending' => 'warning', 'verified' => 'info', 'booked' => 'success', 'rejected' => 'danger', default => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
                TextColumn::make('reference')->searchable()->copyable()->fontFamily('mono')->weight('bold'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('phone')->searchable()->copyable(),
                TextColumn::make('exam')->wrap(),
                TextColumn::make('transaction_id')->label('Transaction ID')->searchable()->placeholder('—')->toggleable(),
                TextColumn::make('redhat_login')->label('Red Hat login')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => ExamBooking::STATUSES[$state] ?? $state)->color(fn ($state) => self::statusColor($state)),
                TextColumn::make('exam_at')->label('Exam date')->dateTime('j M Y, g:i a')->placeholder('—')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ExamBooking::STATUSES),
            ])
            ->recordActions([EditAction::make()->label('Review')->icon('heroicon-o-eye')]);
    }
}
