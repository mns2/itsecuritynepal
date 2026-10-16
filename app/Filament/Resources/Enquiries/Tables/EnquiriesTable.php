<?php

namespace App\Filament\Resources\Enquiries\Tables;

use App\Models\Enquiry;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('phone')->searchable()->copyable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('course.name')->label('Course')->placeholder(fn ($record) => $record->interest ? ucfirst($record->interest) : '—')->wrap(),
                TextColumn::make('audience')->badge()->color(fn ($state) => match ($state) {
                    'Group' => 'warning', 'Corporate' => 'info', default => 'gray'
                }),
                SelectColumn::make('status')->options(Enquiry::STATUSES),
            ])
            ->filters([
                SelectFilter::make('status')->options(Enquiry::STATUSES)->default('new'),
                SelectFilter::make('audience')->options(['Individual' => 'Individual', 'Group' => 'Group', 'Corporate' => 'Corporate']),
                SelectFilter::make('form')->options(Enquiry::FORMS),
            ])
            ->recordActions([EditAction::make()->label('Open')])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('contacted')->label('Mark as contacted')->icon('heroicon-o-phone')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'contacted'])),
                    BulkAction::make('spam')->label('Mark as spam')->icon('heroicon-o-no-symbol')->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'spam'])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
