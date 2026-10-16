<?php

namespace App\Filament\Resources\LearningPaths\Schemas;

use App\Models\Course;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningPathForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Learning path')->columnSpanFull()->schema([
                Grid::make(3)->schema([
                    TextInput::make('name')->required()->columnSpan(2),
                    TextInput::make('key')->required()->unique(ignoreRecord: true),
                    TextInput::make('goal')->columnSpan(2),
                    TextInput::make('icon')->default('route'),
                    TextInput::make('sort')->numeric()->default(0),
                    Toggle::make('is_published')->default(true)->inline(false),
                ]),
                Repeater::make('steps')->schema([
                    Select::make('course_id')->label('Course')->required()->searchable()
                        ->options(fn () => Course::orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->code} · {$c->name}"])),
                    TextInput::make('note')->placeholder('e.g. Foundations, RHCSA'),
                ])->columns(2)->reorderable()->addActionLabel('Add step'),
            ]),
        ]);
    }
}
