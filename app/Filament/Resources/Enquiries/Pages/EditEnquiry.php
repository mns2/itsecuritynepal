<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Students\Schemas\FeeFields;
use App\Filament\Resources\Students\StudentResource;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Student;
use App\Models\TrainingClass;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;

class EditEnquiry extends EditRecord
{
    protected static string $resource = EnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::enrolAction(),
            Action::make('student')->label('View student')->icon('heroicon-o-user')->color('gray')
                ->visible(fn (Enquiry $record) => $record->student()->exists())
                ->url(fn (Enquiry $record) => StudentResource::getUrl('edit', ['record' => $record->student])),
            DeleteAction::make(),
        ];
    }

    /** Turns a lead into a student: course, batch (class) and confirmed fee. Marks the enquiry Enrolled. */
    public static function enrolAction(): Action
    {
        return Action::make('enrol')->label('Enrol as student')->icon('heroicon-o-academic-cap')->color('primary')
            ->visible(fn (Enquiry $record) => ! $record->student()->exists() && $record->status !== 'spam')
            ->modalHeading(fn (Enquiry $record) => 'Enrol '.$record->name)
            ->modalDescription('Creates the student record with batch and fee, and marks this enquiry as Enrolled.')
            ->modalWidth(Width::FourExtraLarge)
            ->fillForm(fn (Enquiry $record) => [
                'course_id' => $record->course_id,
                'enrolled_on' => now()->toDateString(),
                'fee_discount' => 0,
                'fee_paid' => 0,
            ])
            ->schema([
                Section::make('Course and batch')->compact()->schema([
                    Grid::make(3)->schema([
                        Select::make('course_id')->label('Course')->required()->searchable()->live()->columnSpan(2)
                            ->options(fn () => Course::orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->code} · {$c->name}"])),
                        DatePicker::make('enrolled_on')->label('Enrolled on')->required()->native(false),
                        Select::make('training_class_id')->label('Batch / class')->required()->native(false)->columnSpan(3)
                            ->options(fn (Get $get) => TrainingClass::where('course_id', $get('course_id'))->whereIn('status', ['upcoming', 'ongoing'])
                                ->orderBy('starts_on')->get()->mapWithKeys(fn ($c) => [$c->id => $c->name.' · starts '.$c->starts_on->format('j M').($c->schedule ? ' · '.$c->schedule : '').($c->trainer ? ' · '.$c->trainer : '')]))
                            ->helperText('No suitable batch? Use "+" to create one.')
                            ->createOptionForm([
                                Grid::make(2)->schema([
                                    TextInput::make('name')->label('Batch name')->required()->placeholder('e.g. RHCSA · Morning · Nov 2026'),
                                    DatePicker::make('starts_on')->label('Starts')->required()->native(false)->default(now()),
                                    Select::make('instructor_user_id')->label('Instructor')->native(false)
                                        ->options(fn () => \App\Models\User::where('role', 'instructor')->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                                        ->helperText('They will see this batch at /trainer and take attendance.'),
                                    TextInput::make('mode')->placeholder('Classroom / Live Online'),
                                    \Filament\Forms\Components\CheckboxList::make('days')->label('Class days')->options(TrainingClass::DAYS)->columns(7)->columnSpanFull()
                                        ->default(['sun', 'mon', 'tue', 'wed', 'thu', 'fri']),
                                    \Filament\Forms\Components\TimePicker::make('start_time')->label('Class starts')->seconds(false),
                                    \Filament\Forms\Components\TimePicker::make('end_time')->label('Class ends')->seconds(false),
                                ]),
                            ])
                            ->createOptionUsing(fn (array $data, Get $get) => TrainingClass::create($data + [
                                'course_id' => $get('course_id'),
                                'trainer' => filled($data['instructor_user_id'] ?? null) ? \App\Models\User::find($data['instructor_user_id'])?->name : null,
                                'status' => now()->lt($data['starts_on']) ? 'upcoming' : 'ongoing',
                            ])->id),
                    ]),
                ]),
                FeeFields::section(requireConfirmation: true),
            ])
            ->action(function (Enquiry $record, array $data, $livewire) {
                $data = FeeFields::stampConfirmation($data);
                $firstPayment = (int) ($data['fee_paid'] ?? 0);
                $method = $data['payment_method'] ?? 'cash';
                $receipt = $data['receipt_no'] ?? null;
                $data['fee_paid'] = 0;
                $student = Student::create($data + [
                    'name' => $record->name,
                    'phone' => $record->phone,
                    'email' => $record->email ?: null,
                    'enquiry_id' => $record->id,
                    'audience' => $record->audience === 'Corporate' ? 'Corporate Batch' : ($record->audience ?: 'Individual'),
                    'organisation' => $record->organisation,
                    'status' => 'enrolled',
                ]);
                if ($firstPayment > 0) {
                    $student->payments()->create(['amount' => $firstPayment, 'paid_on' => $data['enrolled_on'], 'method' => $method, 'receipt_no' => $receipt, 'note' => 'At enrolment']);
                    $student->refresh();
                }
                $record->update(['status' => 'enrolled']);
                if (method_exists($livewire, 'refreshFormData')) {
                    $livewire->refreshFormData(['status']);
                }
                Notification::make()->success()->title('Student enrolled')
                    ->body($student->trainingClass?->name.($student->feeBalance() ? ' · balance Rs '.number_format($student->feeBalance()) : ' · fee paid'))
                    ->actions([Action::make('open')->label('Open student')->url(StudentResource::getUrl('edit', ['record' => $student]))])->send();
            });
    }
}
