<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Models\Faq;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Question')->columnSpanFull()->schema([
                TextInput::make('question')->required()->maxLength(255),
                Textarea::make('answer')->required()->rows(4),
                Grid::make(4)->schema([
                    Select::make('group')->options(Faq::GROUPS)->required(),
                    TextInput::make('sort')->numeric()->default(0),
                    Toggle::make('show_on_home')->label('Show on homepage')->inline(false),
                    Toggle::make('is_published')->label('Published')->default(true)->inline(false),
                ]),
            ]),
        ]);
    }
}
