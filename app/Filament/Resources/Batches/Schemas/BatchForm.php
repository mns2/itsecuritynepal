<?php

namespace App\Filament\Resources\Batches\Schemas;

use App\Models\Batch;
use App\Models\Course;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Batch')->columnSpanFull()->schema([
                Select::make('course_id')->relationship('course', 'name')->searchable()->preload()->required()
                    ->getOptionLabelFromRecordUsing(fn (Course $c) => "{$c->code} · {$c->name}"),
                Grid::make(3)->schema([
                    DatePicker::make('starts_on')->label('Start date')->native(false)->helperText('Optional exact date'),
                    TextInput::make('start_label')->label('Start (display text)')->required()->placeholder('e.g. Every Monday, 3 Nov 2026'),
                    TextInput::make('schedule')->placeholder('e.g. Mon – Fri · 1:00 – 5:00 PM'),
                    TextInput::make('mode')->placeholder('Classroom / Live Online'),
                    Select::make('status')->options(Batch::STATUSES)->required()->default('open'),
                    TextInput::make('sort')->numeric()->default(0),
                ]),
                Grid::make(2)->schema([
                    Toggle::make('is_published')->label('Published')->default(true),
                    Toggle::make('is_illustrative')->label('Sample data (hide date badge on cards)')->helperText('Turn off once the batch details are confirmed.'),
                ]),
            ]),
        ]);
    }
}
