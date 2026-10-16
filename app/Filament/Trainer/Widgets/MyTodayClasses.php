<?php

namespace App\Filament\Trainer\Widgets;

use App\Filament\Pages\AttendanceRegister;
use App\Filament\Pages\TakeAttendance;
use App\Models\ClassSession;
use App\Models\TrainingClass;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Carbon;

/** Instructor home: today's batches in time order, whether the class is on now, and whether attendance is done. */
class MyTodayClasses extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public function getTableHeading(): string
    {
        return "Today's classes · ".today()->format('l j M');
    }

    public function table(Table $table): Table
    {
        $today = today();
        $ids = TrainingClass::visibleTo(auth()->user())->where('status', 'ongoing')->get()
            ->filter(fn (TrainingClass $c) => $c->meetsOn($today))->pluck('id');

        return $table
            ->query(TrainingClass::query()->whereIn('id', $ids)->with('course')->orderBy('start_time')
                ->withCount(['students as studying_count' => fn ($q) => $q->whereIn('status', ['enrolled', 'completed'])]))
            ->paginated(false)
            ->columns([
                TextColumn::make('start_time')->label('Time')
                    ->formatStateUsing(fn ($state, TrainingClass $r) => $state ? Carbon::parse($state)->format('g:i A').($r->end_time ? '–'.Carbon::parse($r->end_time)->format('g:i A') : '') : '—')
                    ->description(fn (TrainingClass $r) => $this->isNow($r) ? 'Class in progress' : null),
                TextColumn::make('name')->label('Batch')->description(fn (TrainingClass $r) => $r->course?->name)->wrap(),
                TextColumn::make('studying_count')->label('Students')->alignCenter(),
                TextColumn::make('today')->label('Attendance')->badge()
                    ->state(function (TrainingClass $r) use ($today) {
                        $s = ClassSession::where('training_class_id', $r->id)->whereDate('held_on', $today)->withCount([
                            'attendances as attended' => fn ($q) => $q->whereIn('status', ['present', 'late']),
                            'attendances',
                        ])->first();

                        return $s ? "Done · {$s->attended}/{$s->attendances_count} present" : 'Not taken yet';
                    })
                    ->color(fn ($state) => str_starts_with($state, 'Done') ? 'success' : 'warning'),
            ])
            ->recordActions([
                Action::make('take')->label('Take attendance')->icon('heroicon-o-clipboard-document-check')->button()
                    ->url(fn (TrainingClass $r) => TakeAttendance::getUrl(['class' => $r->id], panel: 'trainer')),
                Action::make('register')->label('Register')->icon('heroicon-o-table-cells')->color('gray')
                    ->url(fn (TrainingClass $r) => AttendanceRegister::getUrl(['class' => $r->id], panel: 'trainer')),
            ])
            ->emptyStateHeading('No classes today')
            ->emptyStateDescription('Your batches and their class days are under My classes.');
    }

    private function isNow(TrainingClass $c): bool
    {
        if (! $c->start_time) {
            return false;
        }
        $start = today()->setTimeFromTimeString($c->start_time);
        $end = $c->end_time ? today()->setTimeFromTimeString($c->end_time) : $start->copy()->addHours(2);

        return now()->between($start->copy()->subMinutes(15), $end);
    }
}
