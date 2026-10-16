<?php

namespace App\Filament\Resources\TrainingClasses\Schemas;

use App\Models\Course;
use App\Models\Enquiry;
use App\Models\TrainingClass;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TrainingClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Class')->description('Internal record of a running class. Not shown on the website (use Catalogue → Batches for that).')
                ->columnSpanFull()->schema([
                    Grid::make(3)->schema([
                        Select::make('course_id')->label('Course')->relationship('course', 'name')->searchable()->preload()->required()
                            ->getOptionLabelFromRecordUsing(fn (Course $c) => "{$c->code} · {$c->name}")->columnSpan(2),
                        Select::make('status')->options(TrainingClass::STATUSES)->required()->default('upcoming')->native(false),
                        TextInput::make('name')->label('Class name')->required()->maxLength(160)->placeholder('e.g. RHCSA · Morning · Nov 2026')->columnSpan(2),
                        Select::make('audience')->label('Training for')->options(array_combine(Enquiry::AUDIENCES, Enquiry::AUDIENCES))->default('Individual')->required()->live()->native(false),
                        TextInput::make('organisation')->maxLength(160)->visible(fn (Get $get) => $get('audience') === 'Corporate Batch')->columnSpan(2),
                        Select::make('instructor_user_id')->label('Instructor account')->native(false)->searchable()
                            ->options(fn () => \App\Models\User::where('role', 'instructor')->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->live()->afterStateUpdated(fn ($state, \Filament\Schemas\Components\Utilities\Set $set) => $state ? $set('trainer', \App\Models\User::find($state)?->name) : null)
                            ->helperText('Lets this instructor sign in at /trainer, see the class and take attendance. Add accounts in Settings → Staff accounts.'),
                        TextInput::make('trainer')->label('Trainer name (shown on lists)')->maxLength(120),
                        TextInput::make('mode')->placeholder('Classroom / Live Online'),
                        \Filament\Forms\Components\CheckboxList::make('days')->label('Class days')->options(TrainingClass::DAYS)->columns(7)->columnSpan(3)
                            ->default(['sun', 'mon', 'tue', 'wed', 'thu', 'fri']),
                        \Filament\Forms\Components\TimePicker::make('start_time')->label('Class starts')->seconds(false),
                        \Filament\Forms\Components\TimePicker::make('end_time')->label('Class ends')->seconds(false)->after('start_time'),
                        TextInput::make('schedule')->label('Schedule text (optional)')->placeholder('Empty = built from days and times'),
                        TextInput::make('capacity')->label('Seats')->numeric()->minValue(1),
                        DatePicker::make('starts_on')->label('Starts')->required()->native(false)->default(now()),
                        DatePicker::make('ends_on')->label('Ends')->native(false)->afterOrEqual('starts_on'),
                    ]),
                    Textarea::make('notes')->rows(2),
                ]),
        ]);
    }
}
