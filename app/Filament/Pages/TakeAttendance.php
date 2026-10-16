<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\TrainingClass;
use App\Support\Access;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use UnitEnum;

/**
 * Attendance sheet: a table of the batch's students with Present / Late / Absent / Excused for one date.
 *  - Instructors (/trainer): only their own batches, only today (take it during class).
 *  - Training staff (/admin): any batch, any past date (to view or correct).
 * Opening a date that was already taken loads the saved marks.
 */
class TakeAttendance extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Take attendance';

    protected static ?string $title = 'Take attendance';

    protected static ?string $slug = 'attendance';

    protected string $view = 'filament.pages.take-attendance';

    #[Url(as: 'class')]
    public ?string $classId = null;

    #[Url(as: 'date')]
    public ?string $dateParam = null;

    /** Header form state: training_class_id, held_on, topic */
    public ?array $data = [];

    /** student_id => present|late|absent|excused|'' (unmarked) */
    public array $marks = [];

    /** student_id => note */
    public array $notes = [];

    public bool $existing = false;

    public ?string $takenBy = null;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'trainer' || Access::allows('training');
    }

    /** Instructors (and anyone in the trainer portal) may only mark today. */
    public function todayOnly(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'trainer' || ! Access::allows('training');
    }

    public function mount(): void
    {
        $classId = $this->classId ?: $this->classes()->keys()->first();
        $date = $this->todayOnly() || ! $this->dateParam ? today()->toDateString() : Carbon::parse($this->dateParam)->toDateString();
        $this->form->fill(['training_class_id' => $classId, 'held_on' => $date]);
        $this->loadSheet();
    }

    /** Batches this user may take attendance for: today's batches first. */
    protected function classes()
    {
        return TrainingClass::visibleTo(auth()->user())->whereIn('status', ['ongoing', 'upcoming'])->with('course')->get()
            ->sortBy(fn (TrainingClass $c) => ($c->meetsOn(today()) ? '0' : '1').($c->start_time ?? '99'))
            ->mapWithKeys(fn (TrainingClass $c) => [$c->id => $c->name
                .($c->course ? ' · '.$c->course->code : '')
                .($c->schedule ? ' · '.$c->schedule : '')
                .($c->meetsOn(today()) ? ' · today' : '')]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make()->schema([
                Grid::make(['default' => 1, 'md' => 3])->schema([
                    Select::make('training_class_id')->label('Batch')->options(fn () => $this->classes())->required()->native(false)->live()
                        ->afterStateUpdated(fn () => $this->loadSheet())->columnSpan(['md' => 2])
                        ->placeholder('No batches assigned to you yet'),
                    DatePicker::make('held_on')->label('Date')->required()->native(false)->maxDate(today())->live()
                        ->disabled(fn () => $this->todayOnly())->dehydrated()
                        ->helperText(fn () => $this->todayOnly() ? 'Attendance is taken on the day of the class.' : 'Pick an earlier date to view or correct it.')
                        ->afterStateUpdated(fn () => $this->loadSheet()),
                ]),
                TextInput::make('topic')->label('Topic covered (optional)')->maxLength(200)->placeholder('e.g. LVM and file systems'),
            ]),
        ]);
    }

    public function currentClass(): ?TrainingClass
    {
        $id = $this->data['training_class_id'] ?? null;

        return $id ? TrainingClass::visibleTo(auth()->user())->with('course')->find($id) : null;
    }

    /** Students in the batch, with this date's saved marks (if any). */
    public function loadSheet(): void
    {
        $class = $this->currentClass();
        $date = filled($this->data['held_on'] ?? null) ? Carbon::parse($this->data['held_on'])->toDateString() : null;
        $this->marks = [];
        $this->notes = [];
        $this->existing = false;
        $this->takenBy = null;
        if (! $class || ! $date) {
            return;
        }

        $session = ClassSession::where('training_class_id', $class->id)->whereDate('held_on', $date)->with(['attendances', 'takenBy'])->first();
        $saved = $session?->attendances->keyBy('student_id') ?? collect();
        $this->existing = (bool) $session;
        $this->takenBy = $session?->takenBy?->name;
        $this->data['topic'] = $session?->topic;

        foreach ($this->students($class) as $s) {
            $this->marks[$s->id] = $saved[$s->id]->status ?? '';
            $this->notes[$s->id] = $saved[$s->id]->note ?? '';
        }
    }

    public function students(?TrainingClass $class = null)
    {
        $class ??= $this->currentClass();

        return $class ? $class->students()->whereIn('status', ['enrolled', 'completed'])->orderBy('name')->get() : collect();
    }

    public function markAll(string $status): void
    {
        abort_unless(array_key_exists($status, Attendance::STATUSES), 422);
        foreach (array_keys($this->marks) as $id) {
            $this->marks[$id] = $status;
        }
    }

    public function save(): void
    {
        $state = $this->form->getState();
        // Server-side checks: own batches only; instructors only today; never a future date.
        $class = TrainingClass::visibleTo(auth()->user())->findOrFail($state['training_class_id']);
        $date = $this->todayOnly() ? today()->toDateString() : Carbon::parse($state['held_on'])->toDateString();
        abort_if(Carbon::parse($date)->isAfter(today()), 422);
        $students = $this->students($class)->pluck('id')->all();

        DB::transaction(function () use ($class, $date, $state, $students) {
            // Look up by calendar date (date columns can come back with a time part on some databases)
            $session = ClassSession::where('training_class_id', $class->id)->whereDate('held_on', $date)->first()
                ?? new ClassSession(['training_class_id' => $class->id, 'held_on' => $date]);
            $session->fill(['topic' => $state['topic'] ?? null, 'taken_by' => auth()->id()])->save();
            foreach ($students as $id) {
                $status = $this->marks[$id] ?? '';
                Attendance::updateOrCreate(
                    ['class_session_id' => $session->id, 'student_id' => $id],
                    [
                        'status' => array_key_exists($status, Attendance::STATUSES) ? $status : 'absent', // unmarked = absent
                        'note' => filled($this->notes[$id] ?? null) ? mb_substr($this->notes[$id], 0, 200) : null,
                    ],
                );
            }
        });

        $present = collect($students)->filter(fn ($id) => in_array($this->marks[$id] ?? '', Attendance::ATTENDED, true))->count();
        Notification::make()->success()->title('Attendance saved')
            ->body("{$present} of ".count($students).' present on '.Carbon::parse($date)->format('D j M Y').'.')->send();
        $this->loadSheet();
    }

    protected function getViewData(): array
    {
        $class = $this->currentClass();

        return [
            'class' => $class,
            'students' => $this->students($class),
            'counts' => collect($this->marks)->countBy(fn ($s) => $s ?: 'unmarked'),
            'notClassDay' => $class && ! $class->meetsOn(Carbon::parse($this->data['held_on'] ?? today())),
            'registerUrl' => $class ? AttendanceRegister::getUrl(['class' => $class->id]) : null,
        ];
    }
}
