<?php

namespace App\Filament\Resources\Students\Tables;

use App\Models\Enquiry;
use App\Models\Student;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class StudentsTable
{
    public static function configure(Table $table, bool $inClass = false): Table
    {
        $table = $table
            ->defaultSort('enrolled_on', 'desc')
            ->columns(array_values(array_filter([
                TextColumn::make('name')->searchable()->description(fn (Student $r) => $r->phone),
                $inClass ? null : TextColumn::make('course.name')->label('Course')->wrap()->searchable()
                    ->description(fn (Student $r) => $r->trainingClass?->name),
                $inClass ? null : TextColumn::make('audience')->label('For')->badge()->color(fn ($state) => match ($state) {
                    'Group' => 'warning', 'Corporate Batch' => 'info', default => 'gray'
                })->description(fn (Student $r) => $r->organisation),
                TextColumn::make('enrolled_on')->label('Enrolled')->date('j M Y')->sortable(),
                SelectColumn::make('status')->options(Student::STATUSES)->selectablePlaceholder(false),
                IconColumn::make('certified')->boolean()->alignCenter()->toggleable(),
            ])))
            ->filters(array_values(array_filter([
                SelectFilter::make('status')->options(Student::STATUSES),
                $inClass ? null : SelectFilter::make('course')->relationship('course', 'name')->searchable()->preload(),
                $inClass ? null : SelectFilter::make('audience')->label('Training for')->options(array_combine(Enquiry::AUDIENCES, Enquiry::AUDIENCES)),
            ])))
            ->emptyStateHeading('No students yet')
            ->emptyStateDescription('Add students here, or open an enquiry and press "Enrol as student".');

        if ($inClass) {
            return $table;
        }

        return $table
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('complete')->label('Mark completed')->icon('heroicon-o-check-badge')->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['status' => 'completed', 'completed_on' => now()])),
                DeleteBulkAction::make(),
            ])]);
    }
}
