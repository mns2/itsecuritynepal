<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Student;
use App\Models\TrainingClass;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class StudentForm
{
    /** Shared by the Students screen and the "Students in this class" table (where course and class come from the class). */
    public static function fields(bool $inClass = false): array
    {
        return [
            Section::make('Student')->columnSpanFull()->schema([
                Grid::make(3)->schema(array_values(array_filter([
                    TextInput::make('name')->required()->maxLength(120),
                    TextInput::make('phone')->tel()->maxLength(30),
                    TextInput::make('email')->email()->maxLength(160),
                    $inClass ? null : Select::make('course_id')->label('Course')->relationship('course', 'name')->searchable()->preload()->required()
                        ->getOptionLabelFromRecordUsing(fn (Course $c) => "{$c->code} · {$c->name}")->live()->columnSpan(2),
                    $inClass ? null : Select::make('training_class_id')->label('Batch / class')->searchable()->native(false)
                        ->options(fn (Get $get) => TrainingClass::query()->when($get('course_id'), fn ($q, $id) => $q->where('course_id', $id))
                            ->whereIn('status', ['upcoming', 'ongoing'])->orderByDesc('starts_on')->pluck('name', 'id'))
                        ->helperText('Optional: the running class they attend.'),
                    $inClass ? null : Select::make('audience')->label('Training for')->options(array_combine(Enquiry::AUDIENCES, Enquiry::AUDIENCES))->default('Individual')->required()->live()->native(false),
                    $inClass ? null : TextInput::make('organisation')->maxLength(160)->visible(fn (Get $get) => $get('audience') === 'Corporate Batch'),
                ]))),
                Grid::make(4)->schema([
                    Select::make('status')->options(Student::STATUSES)->default('enrolled')->required()->native(false)->live()
                        ->afterStateUpdated(fn (?string $state, Set $set, Get $get) => $state === 'completed' && ! $get('completed_on') ? $set('completed_on', now()->toDateString()) : null),
                    DatePicker::make('enrolled_on')->label('Enrolled on')->required()->native(false)->default(now()),
                    DatePicker::make('completed_on')->label('Completed on')->native(false)->visible(fn (Get $get) => $get('status') === 'completed'),
                    Toggle::make('certified')->label('Passed certification exam')->inline(false)->visible(fn (Get $get) => $get('status') === 'completed'),
                ]),
                Textarea::make('notes')->rows(2),
            ]),
            FeeFields::section(),
        ];
    }
}
