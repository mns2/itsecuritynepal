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
            Section::make('Career pathway page')->columnSpanFull()->collapsible()
                ->description(fn (?\App\Models\LearningPath $record) => $record?->slug ? 'Public page: /career-pathways/'.$record->slug.'/' : 'Public page at /career-pathways/{slug}/')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true)->prefix('/career-pathways/')->suffix('/'),
                        \Filament\Forms\Components\FileUpload::make('image')->label('Photo (optional)')->hintIcon('heroicon-m-photo')->hint('1600 × 1200 px (4:3)')->image()->disk('public')->directory('pathways')->maxSize(4096),
                    ]),
                    \Filament\Forms\Components\Textarea::make('intro')->label('Who it is for')->rows(2),
                    \Filament\Forms\Components\Textarea::make('role_text')->label('What the job involves')->rows(3),
                    Grid::make(2)->schema([
                        \Filament\Forms\Components\TagsInput::make('skills')->label('Skills you will build'),
                        \Filament\Forms\Components\TagsInput::make('job_titles')->label('Typical job titles'),
                    ]),
                    TextInput::make('salary_note')->label('Salary / demand note (optional)')->maxLength(250)
                        ->helperText('Only add figures you can back up (e.g. from a named survey). Leave empty to hide.'),
                    Grid::make(2)->schema([
                        TextInput::make('meta_title')->label('Google title')->maxLength(70),
                        \Filament\Forms\Components\Textarea::make('meta_description')->label('Google description')->rows(2)->maxLength(320),
                    ]),
                ]),
            \App\Filament\Schemas\SeoFields::relationshipSection(),
        ]);
    }
}
