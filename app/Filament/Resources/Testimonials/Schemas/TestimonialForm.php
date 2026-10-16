<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Testimonial')->columnSpanFull()
                ->description('Only genuine reviews. A testimonial is shown on the website only when both "Consent recorded" and "Published" are on.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->required(),
                        Select::make('course_id')->relationship('course', 'name')->searchable()->preload(),
                    ]),
                    Textarea::make('quote')->required()->rows(4),
                    TextInput::make('outcome')->placeholder('e.g. Now working as a Linux system administrator'),
                    Grid::make(3)->schema([
                        Toggle::make('consent_given')->label('Consent recorded')->inline(false),
                        Toggle::make('is_published')->label('Published')->inline(false),
                        TextInput::make('sort')->numeric()->default(0),
                    ]),
                ]),
        ]);
    }
}
