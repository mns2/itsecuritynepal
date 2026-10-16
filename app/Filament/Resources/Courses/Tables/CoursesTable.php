<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Models\Course;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('code')->badge()->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable()->wrap(),
                TextColumn::make('category.name')->label('Track')->sortable(),
                TextColumn::make('level')->sortable(),
                TextColumn::make('batches_count')->counts('batches')->label('Batches'),
                ToggleColumn::make('featured'),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('level')->options(array_combine(Course::LEVELS, Course::LEVELS)),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('featured'),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')->url(fn (Course $r) => $r->url())->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
