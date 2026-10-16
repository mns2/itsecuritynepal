<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('question')->searchable()->wrap(),
                TextColumn::make('group')->badge()->formatStateUsing(fn ($state) => Faq::GROUPS[$state] ?? $state),
                ToggleColumn::make('show_on_home')->label('Home'),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->filters([SelectFilter::make('group')->options(Faq::GROUPS)])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
