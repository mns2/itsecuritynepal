<?php

namespace App\Filament\Resources\TrainingClasses\Tables;

use App\Models\TrainingClass;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TrainingClassesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount([
                'students as studying_count' => fn ($s) => $s->where('status', 'enrolled'),
                'students as completed_count' => fn ($s) => $s->where('status', 'completed'),
            ]))
            ->columns([
                TextColumn::make('name')->searchable()->wrap()->description(fn (TrainingClass $r) => $r->course?->name),
                TextColumn::make('audience')->label('For')->badge()->color(fn ($state) => match ($state) {
                    'Group' => 'warning', 'Corporate Batch' => 'info', default => 'gray'
                })->description(fn (TrainingClass $r) => $r->organisation),
                TextColumn::make('trainer')->toggleable()->placeholder('—'),
                TextColumn::make('starts_on')->label('Starts')->date('j M Y')->sortable(),
                TextColumn::make('ends_on')->label('Ends')->date('j M Y')->sortable()->placeholder('—'),
                TextColumn::make('studying_count')->label('Studying')->alignCenter()
                    ->description(fn (TrainingClass $r) => $r->capacity ? "of {$r->capacity} seats" : null),
                TextColumn::make('completed_count')->label('Completed')->alignCenter(),
                SelectColumn::make('status')->options(TrainingClass::STATUSES)->selectablePlaceholder(false),
            ])
            ->filters([
                SelectFilter::make('status')->options(TrainingClass::STATUSES),
                SelectFilter::make('course')->relationship('course', 'name')->searchable()->preload(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('register')->label('Register')->icon('heroicon-o-table-cells')->color('gray')
                    ->url(fn (TrainingClass $r) => \App\Filament\Pages\AttendanceRegister::getUrl(['class' => $r->id])),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No classes yet')
            ->emptyStateDescription('Add each running class here to see ongoing classes and student numbers on the dashboard.');
    }
}
