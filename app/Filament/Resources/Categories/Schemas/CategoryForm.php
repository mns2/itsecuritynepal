<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\LearningPath;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Track')->columnSpanFull()->schema([
                Grid::make(3)->schema([
                    TextInput::make('name')->required()->maxLength(120)->columnSpan(2),
                    TextInput::make('short_name')->required()->maxLength(40),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->helperText('URL: /slug/. Do not change existing slugs.'),
                    TextInput::make('key')->required()->unique(ignoreRecord: true)->helperText('Internal id used by filters; never change.'),
                    TextInput::make('icon')->required()->default('layers')->helperText('Lucide icon name, e.g. server, cloud, shield-check'),
                ]),
                FileUpload::make('image')->label('Track photo')->hintIcon('heroicon-m-photo')->hint('1280 × 720 px (16:9)')->image()->disk('public')->directory('tracks')->imageEditor()->maxSize(4096)
                    ->helperText('Used on course cards in this track (unless a course has its own photo). Landscape, at least 1000 px wide.'),
                TextInput::make('tagline')->maxLength(160),
                Textarea::make('intro')->rows(3),
                Repeater::make('objectives')->label("What you'll learn")->simple(TextInput::make('text')->required())->reorderable(),
                Grid::make(3)->schema([
                    Select::make('learning_path_key')->label('Recommended learning path')->options(fn () => LearningPath::pluck('name', 'key'))->native(false),
                    TextInput::make('sort')->numeric()->default(0),
                    Toggle::make('show_on_home')->label('Show as a homepage track tile')->inline(false),
                ]),
            ]),
            Section::make('FAQs')->columnSpanFull()->collapsed()->schema([
                Repeater::make('faqs')->hiddenLabel()->schema([
                    TextInput::make('q')->label('Question')->required(),
                    Textarea::make('a')->label('Answer')->rows(3)->required(),
                ])->itemLabel(fn (array $state) => $state['q'] ?? null)->collapsible()->reorderable(),
            ]),
            Section::make('SEO')->columnSpanFull()->collapsed()->schema([
                TextInput::make('meta_title')->maxLength(70),
                Textarea::make('meta_description')->rows(2)->maxLength(320),
            ]),
            \App\Filament\Schemas\SeoFields::relationshipSection(),
        ]);
    }
}
