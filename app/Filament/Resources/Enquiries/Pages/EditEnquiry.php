<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Students\StudentResource;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Student;
use App\Models\TrainingClass;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Utilities\Get;

class EditEnquiry extends EditRecord
{
    protected static string $resource = EnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enrol')->label('Enrol as student')->icon('heroicon-o-academic-cap')->color('primary')
                ->visible(fn (Enquiry $record) => ! $record->student()->exists())
                ->modalDescription('Creates a student record from this enquiry and marks the enquiry as Enrolled.')
                ->fillForm(fn (Enquiry $record) => ['course_id' => $record->course_id, 'enrolled_on' => now()->toDateString()])
                ->schema([
                    Select::make('course_id')->label('Course')->required()->searchable()->live()
                        ->options(fn () => Course::orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->code} · {$c->name}"])),
                    Select::make('training_class_id')->label('Class (optional)')->native(false)
                        ->options(fn (Get $get) => TrainingClass::where('course_id', $get('course_id'))->whereIn('status', ['upcoming', 'ongoing'])->pluck('name', 'id')),
                    DatePicker::make('enrolled_on')->label('Enrolled on')->required()->native(false),
                ])
                ->action(function (Enquiry $record, array $data) {
                    $student = Student::create($data + [
                        'name' => $record->name,
                        'phone' => $record->phone,
                        'email' => $record->email,
                        'enquiry_id' => $record->id,
                        'audience' => $record->audience === 'Corporate' ? 'Corporate Batch' : ($record->audience ?: 'Individual'),
                        'organisation' => $record->organisation,
                        'status' => 'enrolled',
                    ]);
                    $record->update(['status' => 'enrolled']);
                    $this->refreshFormData(['status']);
                    Notification::make()->success()->title('Student added')
                        ->actions([Action::make('open')->label('Open student')->url(StudentResource::getUrl('edit', ['record' => $student]))])->send();
                }),
            Action::make('student')->label('View student')->icon('heroicon-o-user')->color('gray')
                ->visible(fn (Enquiry $record) => $record->student()->exists())
                ->url(fn (Enquiry $record) => StudentResource::getUrl('edit', ['record' => $record->student])),
            DeleteAction::make(),
        ];
    }
}
