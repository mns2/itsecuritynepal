<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TrainingClasses\TrainingClassResource;
use App\Models\TrainingClass;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Ongoing classes first, then classes starting soon. */
class OngoingClasses extends TableWidget
{
    public static function canView(): bool
    {
        return \App\Support\Access::allows('training');
    }

    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Ongoing and upcoming classes';

    public function table(Table $table): Table
    {
        return $table
            ->query(TrainingClass::query()->with('course')->whereIn('status', ['ongoing', 'upcoming'])
                ->withCount(['students as studying_count' => fn ($q) => $q->where('status', 'enrolled')])
                ->orderByRaw("case when status = 'ongoing' then 0 else 1 end")->orderBy('starts_on')->limit(8))
            ->paginated(false)
            ->recordUrl(fn (TrainingClass $r) => TrainingClassResource::getUrl('edit', ['record' => $r]))
            ->headerActions([
                Action::make('all')->label('All classes')->url(TrainingClassResource::getUrl('index'))->color('gray')->size('sm'),
                Action::make('add')->label('Add class')->url(TrainingClassResource::getUrl('create'))->icon('heroicon-m-plus')->size('sm'),
            ])
            ->columns([
                TextColumn::make('name')->label('Class')->description(fn (TrainingClass $r) => $r->course?->name)->wrap(),
                TextColumn::make('audience')->label('For')->badge()->color(fn ($state) => match ($state) {
                    'Group' => 'warning', 'Corporate Batch' => 'info', default => 'gray'
                }),
                TextColumn::make('trainer')->placeholder('—'),
                TextColumn::make('schedule')->placeholder('—'),
                TextColumn::make('studying_count')->label('Students')->alignCenter()
                    ->formatStateUsing(fn ($state, TrainingClass $r) => $r->capacity ? "{$state} / {$r->capacity}" : (string) $state),
                TextColumn::make('starts_on')->label('Dates')->date('j M')
                    ->description(fn (TrainingClass $r) => $r->ends_on ? 'until '.$r->ends_on->format('j M Y') : null),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => TrainingClass::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => $state === 'ongoing' ? 'success' : 'warning'),
            ])
            ->emptyStateHeading('No ongoing or upcoming classes')
            ->emptyStateDescription('Add your running classes under Training → Classes to track them here.')
            ->emptyStateIcon('heroicon-o-presentation-chart-bar');
    }
}
