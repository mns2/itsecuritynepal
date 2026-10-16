<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('slug')->formatStateUsing(fn ($state) => "/{$state}/"),
                TextColumn::make('courses_count')->counts('courses')->label('Courses'),
                ToggleColumn::make('show_on_home')->label('Homepage tile'),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')->url(fn (Category $r) => $r->url())->openUrlInNewTab(),
                EditAction::make(),
            ]);
    }
}
