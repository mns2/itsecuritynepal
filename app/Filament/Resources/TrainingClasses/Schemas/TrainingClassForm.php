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
                        TextInput::make('trainer')->maxLength(120),
                        TextInput::make('mode')->placeholder('Classroom / Live Online'),
                        TextInput::make('schedule')->placeholder('e.g. Sun – Fri · 7:00 – 9:00 AM'),
                        TextInput::make('capacity')->label('Seats')->numeric()->minValue(1),
                        DatePicker::make('starts_on')->label('Starts')->required()->native(false)->default(now()),
                        DatePicker::make('ends_on')->label('Ends')->native(false)->afterOrEqual('starts_on'),
                    ]),
                    Textarea::make('notes')->rows(2),
                ]),
        ]);
    }
}
