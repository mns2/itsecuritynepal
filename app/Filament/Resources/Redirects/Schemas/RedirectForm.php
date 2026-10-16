<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Redirect')->columnSpanFull()
                ->description('Send visitors (and Google) from an old URL to its new address. Use leading and trailing slashes: /old-page/.')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('from_path')->required()->unique(ignoreRecord: true)->regex('#^/.*/$#')->placeholder('/old-page/'),
                        TextInput::make('to_path')->required()->placeholder('/new-page/ or https://…'),
                        Select::make('status_code')->options([301 => '301 Permanent', 302 => '302 Temporary'])->default(301)->required(),
                    ]),
                    TextInput::make('hits')->numeric()->disabled()->dehydrated(false),
                ]),
        ]);
    }
}
