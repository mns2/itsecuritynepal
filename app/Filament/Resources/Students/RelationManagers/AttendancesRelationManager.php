<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Models\Attendance;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** One student's attendance history (read-only; marks are taken on the attendance sheet). */
class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Attendance';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['session.trainingClass', 'session.takenBy'])
                ->join('class_sessions', 'class_sessions.id', '=', 'attendances.class_session_id')
                ->orderByDesc('class_sessions.held_on')->select('attendances.*'))
            ->description(fn () => ($rate = $this->getOwnerRecord()->attendanceRate()) === null ? 'No attendance recorded yet.' : "Attendance: {$rate}% (present + late)")
            ->columns([
                TextColumn::make('session.held_on')->label('Date')->date('D j M Y'),
                TextColumn::make('session.trainingClass.name')->label('Batch')->wrap(),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => Attendance::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'present' => 'success', 'late' => 'warning', 'absent' => 'danger', default => 'gray' }),
                TextColumn::make('note')->placeholder('—')->wrap(),
                TextColumn::make('session.topic')->label('Topic')->placeholder('—')->toggleable(),
                TextColumn::make('session.takenBy.name')->label('Taken by')->placeholder('—')->toggleable(),
            ])
            ->filters([SelectFilter::make('status')->options(Attendance::STATUSES)])
            ->emptyStateHeading('No attendance recorded');
    }
}
