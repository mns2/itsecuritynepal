<?php

namespace App\Filament\Resources\TrainingMethods\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Training method')->columnSpanFull()->schema([
                Grid::make(3)->schema([
                    TextInput::make('name')->required()->columnSpan(2),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->helperText('URL: /slug/'),
                    TextInput::make('short')->label('Short description')->columnSpan(2),
                    TextInput::make('icon')->default('presentation'),
                ]),
                Textarea::make('body')->rows(3),
                TextInput::make('best_for'),
                Repeater::make('features')->simple(TextInput::make('text')->required())->reorderable(),
                Grid::make(2)->schema([
                    TextInput::make('sort')->numeric()->default(0),
                    Toggle::make('is_published')->default(true)->inline(false),
                ]),
            ]),
        ]);
    }
}
