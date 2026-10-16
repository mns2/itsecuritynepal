<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\TrainingClass;
use App\Support\Access;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use UnitEnum;

/** Attendance register: students (rows) × class dates (columns) for one batch, with totals and CSV export. */
class AttendanceRegister extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Attendance register';

    protected static ?string $title = 'Attendance register';

    protected static ?string $slug = 'attendance-register';

    protected string $view = 'filament.pages.attendance-register';

    #[Url(as: 'class')]
    public ?string $classId = null;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'trainer' || Access::allows('training');
    }

    public function mount(): void
    {
        $this->form->fill([
            'training_class_id' => $this->classId ?: TrainingClass::visibleTo(auth()->user())->orderByRaw("case when status = 'ongoing' then 0 else 1 end")->latest('starts_on')->value('id'),
            'from' => null,
            'until' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make()->schema([
                Grid::make(['default' => 1, 'md' => 4])->schema([
                    Select::make('training_class_id')->label('Batch')->native(false)->searchable()->live()->columnSpan(['md' => 2])
                        ->options(fn () => TrainingClass::visibleTo(auth()->user())->with('course')->latest('starts_on')->get()
                            ->mapWithKeys(fn ($c) => [$c->id => $c->name.($c->course ? ' · '.$c->course->code : '').' ('.(TrainingClass::STATUSES[$c->status] ?? $c->status).')'])),
                    DatePicker::make('from')->label('From')->native(false)->live(),
                    DatePicker::make('until')->label('Until')->native(false)->live(),
                ]),
            ]),
        ]);
    }

    /** @return array{class: ?TrainingClass, sessions: \Illuminate\Support\Collection, rows: \Illuminate\Support\Collection} */
    public function register(): array
    {
        $class = ($id = $this->data['training_class_id'] ?? null) ? TrainingClass::visibleTo(auth()->user())->with('course')->find($id) : null;
        if (! $class) {
            return ['class' => null, 'sessions' => collect(), 'rows' => collect()];
        }

        $sessions = ClassSession::where('training_class_id', $class->id)
            ->when($this->data['from'] ?? null, fn ($q, $d) => $q->whereDate('held_on', '>=', $d))
            ->when($this->data['until'] ?? null, fn ($q, $d) => $q->whereDate('held_on', '<=', $d))
            ->with('attendances')->orderBy('held_on')->get();

        $rows = $class->students()->orderBy('name')->get()->map(function ($s) use ($sessions) {
            $marks = $sessions->mapWithKeys(fn ($sess) => [$sess->id => $sess->attendances->firstWhere('student_id', $s->id)?->status]);
            $taken = $marks->filter()->count();
            $attended = $marks->filter(fn ($m) => in_array($m, Attendance::ATTENDED, true))->count();

            return [
                'student' => $s,
                'marks' => $marks,
                'present' => $marks->filter(fn ($m) => $m === 'present')->count(),
                'late' => $marks->filter(fn ($m) => $m === 'late')->count(),
                'absent' => $marks->filter(fn ($m) => $m === 'absent')->count(),
                'excused' => $marks->filter(fn ($m) => $m === 'excused')->count(),
                'rate' => $taken ? (int) round($attended / $taken * 100) : null,
            ];
        });

        return ['class' => $class, 'sessions' => $sessions, 'rows' => $rows];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('take')->label('Take attendance')->icon('heroicon-o-clipboard-document-check')
                ->url(fn () => TakeAttendance::getUrl(array_filter(['class' => $this->data['training_class_id'] ?? null]))),
            Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->action(function () {
                    ['class' => $class, 'sessions' => $sessions, 'rows' => $rows] = $this->register();
                    abort_unless($class, 404);

                    return response()->streamDownload(function () use ($sessions, $rows) {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, array_merge(['student', 'phone'], $sessions->map(fn ($s) => $s->held_on->toDateString())->all(), ['present', 'late', 'absent', 'excused', 'attendance_%']));
                        foreach ($rows as $r) {
                            fputcsv($out, array_merge([$r['student']->name, $r['student']->phone],
                                $r['marks']->map(fn ($m) => $m ? strtoupper($m[0]) : '')->values()->all(),
                                [$r['present'], $r['late'], $r['absent'], $r['excused'], $r['rate']]));
                        }
                        fclose($out);
                    }, 'attendance-'.str($class->name)->slug().'-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
                }),
        ];
    }

    protected function getViewData(): array
    {
        return $this->register();
    }
}
