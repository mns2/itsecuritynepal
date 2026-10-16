<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\Course;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Course')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('Basics')->icon('heroicon-o-academic-cap')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')->required()->maxLength(160)->columnSpan(2),
                        TextInput::make('code')->required()->maxLength(30)->helperText('e.g. RH124, AZ-104, or a short label'),
                        TextInput::make('slug')->required()->maxLength(160)->unique(ignoreRecord: true)
                            ->helperText('URL: /slug/. Keep existing slugs unchanged to protect Google rankings.')->columnSpan(2),
                        Toggle::make('official_code')->label('Code is an official exam/course code')->inline(false)->default(true),
                        Select::make('category_id')->relationship('category', 'name')->required()->preload(),
                        Select::make('level')->options(array_combine(Course::LEVELS, Course::LEVELS))->required(),
                        TextInput::make('duration')->placeholder('e.g. 5 days · 20 hours')->helperText('Leave empty unless confirmed'),
                        TextInput::make('certification')->maxLength(160)->columnSpan(2),
                        CheckboxList::make('modes')->options(array_combine(Course::MODES, Course::MODES))->columns(3)->default(Course::MODES),
                    ]),
                    Textarea::make('summary')->rows(3)->maxLength(400)->helperText('Shown on course cards and as the page intro.'),
                    FileUpload::make('image')->label('Card photo (optional)')->hintIcon('heroicon-m-photo')->hint('1280 × 720 px (16:9)')->image()->disk('public')->directory('courses')->imageEditor()->maxSize(4096)
                        ->helperText('Shown on course cards. If empty, the track photo is used.'),
                    Grid::make(3)->schema([
                        Toggle::make('is_published')->label('Published')->default(true),
                        Toggle::make('featured')->label('Featured on homepage'),
                        TextInput::make('sort')->numeric()->default(0),
                    ]),
                ]),
                Tab::make('Content')->icon('heroicon-o-document-text')->schema([
                    Repeater::make('overview')->label('Overview paragraphs')->simple(Textarea::make('text')->rows(4)->required())->defaultItems(1)->reorderable(),
                    Repeater::make('outcomes')->label('Learning outcomes')->simple(TextInput::make('text')->required())->reorderable()->collapsible(),
                    Repeater::make('prerequisites')->simple(TextInput::make('text')->required())->reorderable(),
                    TagsInput::make('benefits')->label('Highlights')->placeholder('Add highlight'),
                    TagsInput::make('careers')->label('Career opportunities')->placeholder('Add role'),
                ]),
                Tab::make('Curriculum')->icon('heroicon-o-list-bullet')->schema([
                    Repeater::make('modules')->label('Modules')->schema([
                        TextInput::make('title')->required(),
                        Repeater::make('topics')->simple(TextInput::make('text')->required())->reorderable()->defaultItems(1),
                    ])->itemLabel(fn (array $state) => $state['title'] ?? null)->collapsible()->collapsed()->reorderable()->cloneable(),
                    Repeater::make('extra_sections')->label('Extra sections (optional)')->schema([
                        TextInput::make('heading')->required(),
                        Repeater::make('items')->simple(TextInput::make('text')->required()),
                    ])->itemLabel(fn (array $state) => $state['heading'] ?? null)->collapsible()->collapsed(),
                ]),
                Tab::make('FAQs')->icon('heroicon-o-question-mark-circle')->schema([
                    Repeater::make('faqs')->label('Course FAQs')->schema([
                        TextInput::make('q')->label('Question')->required(),
                        Textarea::make('a')->label('Answer')->rows(3)->required(),
                    ])->itemLabel(fn (array $state) => $state['q'] ?? null)->collapsible()->collapsed()->reorderable(),
                ]),
                Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make()->schema([
                        TextInput::make('meta_title')->maxLength(70)->helperText('Leave empty to use "CODE: Name Course in Nepal | IT Security Nepal".'),
                        Textarea::make('meta_description')->rows(2)->maxLength(320)->helperText('Leave empty to use the summary (≈155 characters shown in Google).'),
                    ]),
                ]),
            ]),
            \App\Filament\Schemas\SeoFields::relationshipSection(),
        ]);
    }
}
