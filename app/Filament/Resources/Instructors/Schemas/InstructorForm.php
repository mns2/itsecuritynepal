<?php

namespace App\Filament\Resources\Instructors\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class InstructorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Instructor')->columnSpanFull()
                ->description('Publish only verified profiles, with the instructor\'s approval of their bio and photo.')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')->required()->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set, $get) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->helperText('URL: /instructors/slug/'),
                        TextInput::make('role')->placeholder('e.g. Red Hat & Linux lead instructor'),
                    ]),
                    Textarea::make('bio')->rows(5),
                    Grid::make(2)->schema([
                        TagsInput::make('expertise')->placeholder('e.g. RHCSA'),
                        TagsInput::make('certifications')->placeholder('e.g. Red Hat Certified Engineer'),
                    ]),
                    Grid::make(3)->schema([
                        FileUpload::make('photo')->hintIcon('heroicon-m-photo')->hint('600 × 600 px (square)')->image()->avatar()->disk('public')->directory('instructors')->maxSize(1024),
                        Select::make('category_id')->relationship('category', 'name')->label('Main track'),
                        TextInput::make('linkedin_url')->url()->label('LinkedIn URL'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('sort')->numeric()->default(0),
                        Toggle::make('is_published')->label('Published')->inline(false),
                    ]),
                ]),
        ]);
    }
}
