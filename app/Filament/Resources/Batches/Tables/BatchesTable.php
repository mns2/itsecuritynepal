<?php

namespace App\Filament\Resources\Batches\Tables;

use App\Models\Batch;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('course.name')->label('Course')->searchable()->wrap(),
                TextColumn::make('start_label')->label('Starts'),
                TextColumn::make('schedule'),
                SelectColumn::make('status')->options(Batch::STATUSES),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->filters([SelectFilter::make('status')->options(Batch::STATUSES)])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
