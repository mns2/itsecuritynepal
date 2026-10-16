<?php

namespace App\Filament\Resources\TrainingClasses\RelationManagers;

use App\Filament\Pages\TakeAttendance;
use App\Models\Attendance;
use App\Models\ClassSession;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    protected static ?string $title = 'Attendance';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('held_on', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('takenBy')->withCount([
                'attendances',
                'attendances as attended_count' => fn ($q) => $q->whereIn('status', Attendance::ATTENDED),
            ]))
            ->columns([
                TextColumn::make('held_on')->label('Date')->date('D j M Y')->sortable(),
                TextColumn::make('topic')->placeholder('—')->wrap(),
                TextColumn::make('present')->label('Present')
                    ->state(fn (ClassSession $r) => "{$r->attended_count} / {$r->attendances_count}")
                    ->description(fn (ClassSession $r) => $r->attendances_count ? round($r->attended_count / $r->attendances_count * 100).'%' : null),
                TextColumn::make('takenBy.name')->label('Taken by')->placeholder('—'),
            ])
            ->headerActions([
                Action::make('register')->label('Attendance register')->icon('heroicon-o-table-cells')->color('gray')
                    ->url(fn () => \App\Filament\Pages\AttendanceRegister::getUrl(['class' => $this->getOwnerRecord()->id])),
                Action::make('take')->label('Take attendance')->icon('heroicon-o-clipboard-document-check')
                    ->url(fn () => TakeAttendance::getUrl(['class' => $this->getOwnerRecord()->id])),
            ])
            ->recordActions([
                Action::make('details')->label('Details')->icon('heroicon-o-eye')->color('gray')
                    ->modalSubmitAction(false)->modalCancelActionLabel('Close')
                    ->modalHeading(fn (ClassSession $r) => 'Attendance · '.$r->held_on->format('D j M Y'))
                    ->modalContent(fn (ClassSession $r) => new HtmlString('<ul style="display:grid;gap:.4rem">'.$r->attendances()->with('student')->get()
                        ->sortBy('student.name')
                        ->map(fn ($a) => '<li><strong>'.e($a->student?->name).'</strong>: '.e(Attendance::STATUSES[$a->status] ?? $a->status).($a->note ? ' <em>('.e($a->note).')</em>' : '').'</li>')
                        ->join('').'</ul>')),
                Action::make('edit')->label('Edit')->icon('heroicon-o-pencil-square')->color('gray')
                    ->url(fn (ClassSession $r) => TakeAttendance::getUrl(['class' => $r->training_class_id, 'date' => $r->held_on->toDateString()])),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('No attendance taken yet');
    }
}
