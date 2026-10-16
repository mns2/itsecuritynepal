<?php

namespace App\Filament\Trainer\Resources\MyClasses;

use App\Filament\Pages\TakeAttendance;
use App\Filament\Trainer\Resources\MyClasses\Pages\ListMyClasses;
use App\Filament\Trainer\Resources\MyClasses\Pages\ViewMyClass;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\TrainingClass;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Read-only list of the signed-in instructor's classes, with students and attendance. */
class MyClassResource extends Resource
{
    protected static ?string $model = TrainingClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static ?string $navigationLabel = 'My classes';

    protected static ?string $modelLabel = 'class';

    protected static ?string $slug = 'classes';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user())->with('course')
            ->withCount([
                'students as studying_count' => fn ($q) => $q->whereIn('status', ['enrolled', 'completed']),
                'sessions',
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on', 'desc')
            ->columns([
                TextColumn::make('name')->label('Class')->description(fn (TrainingClass $r) => $r->course?->name)->wrap()->searchable(),
                TextColumn::make('schedule')->placeholder('—'),
                TextColumn::make('starts_on')->label('Dates')->date('j M')
                    ->description(fn (TrainingClass $r) => $r->ends_on ? 'until '.$r->ends_on->format('j M Y') : null),
                TextColumn::make('studying_count')->label('Students')->alignCenter(),
                TextColumn::make('sessions_count')->label('Sessions')->alignCenter(),
                TextColumn::make('rate')->label('Attendance')->alignCenter()
                    ->state(fn (TrainingClass $r) => ($rate = $r->attendanceRate()) === null ? '—' : $rate.'%'),
                TextColumn::make('status')->badge()->formatStateUsing(fn ($state) => TrainingClass::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'ongoing' => 'success', 'upcoming' => 'warning', default => 'gray' }),
            ])
            ->recordActions([
                Action::make('register')->label('Register')->icon('heroicon-o-table-cells')->color('gray')
                    ->url(fn (TrainingClass $r) => \App\Filament\Pages\AttendanceRegister::getUrl(['class' => $r->id])),
                Action::make('attendance')->label('Take attendance')->icon('heroicon-o-clipboard-document-check')->color('primary')
                    ->visible(fn (TrainingClass $r) => in_array($r->status, ['ongoing', 'upcoming'], true))
                    ->url(fn (TrainingClass $r) => TakeAttendance::getUrl(['class' => $r->id])),
                ViewAction::make(),
            ])
            ->emptyStateHeading('No classes assigned to you yet')
            ->emptyStateDescription('Ask the office to set you as the instructor of your classes.');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Grid::make(4)->schema([
                    TextEntry::make('course.name')->label('Course'),
                    TextEntry::make('schedule')->placeholder('—'),
                    TextEntry::make('starts_on')->label('Starts')->date('j M Y'),
                    TextEntry::make('ends_on')->label('Ends')->date('j M Y')->placeholder('—'),
                ]),
            ]),
            Section::make('Students')->columnSpanFull()->schema([
                RepeatableEntry::make('students')->hiddenLabel()
                    ->state(fn (TrainingClass $record) => $record->students()->whereIn('status', ['enrolled', 'completed'])->orderBy('name')->get())
                    ->schema([
                        TextEntry::make('name')->hiddenLabel()->weight('bold'),
                        TextEntry::make('phone')->hiddenLabel()->placeholder('—')->url(fn ($state) => $state ? 'tel:'.$state : null),
                        TextEntry::make('rate')->hiddenLabel()
                            ->state(fn (Student $record) => ($rate = $record->attendanceRate()) === null ? 'No attendance yet' : "Attendance {$rate}%"),
                    ])->columns(3)->contained(false),
            ]),
            Section::make('Attendance history')->columnSpanFull()->schema([
                RepeatableEntry::make('sessions')->hiddenLabel()
                    ->state(fn (TrainingClass $record) => $record->sessions()->withCount([
                        'attendances as attended_count' => fn ($q) => $q->whereIn('status', Attendance::ATTENDED),
                        'attendances',
                    ])->latest('held_on')->limit(60)->get())
                    ->schema([
                        TextEntry::make('held_on')->hiddenLabel()->date('D j M Y')->weight('bold'),
                        TextEntry::make('topic')->hiddenLabel()->placeholder('—'),
                        TextEntry::make('attended')->hiddenLabel()->state(fn ($record) => "{$record->attended_count} / {$record->attendances_count} present"),
                    ])->columns(3)->contained(false),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyClasses::route('/'),
            'view' => ViewMyClass::route('/{record}'),
        ];
    }
}
