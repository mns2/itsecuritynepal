<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Legal page')->columnSpanFull()
                ->description('Enter the approved text (e.g. from your legal advisor). Until text is entered the page shows a short notice and is hidden from search engines.')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('title')->required(),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->helperText('URL: /slug/'),
                        DatePicker::make('last_updated')->native(false),
                    ]),
                    MarkdownEditor::make('body')->columnSpanFull(),
                    Textarea::make('meta_description')->rows(2)->maxLength(320),
                    Toggle::make('is_published')->default(true),
                ]),
            \App\Filament\Schemas\SeoFields::relationshipSection(),
        ]);
    }
}
