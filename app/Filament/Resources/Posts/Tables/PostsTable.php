<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('category')->badge(),
                TextColumn::make('published_at')->dateTime('j M Y')->sortable()->placeholder('Draft'),
            ])
            ->recordActions([
                Action::make('view')->label('View')->icon('heroicon-o-arrow-top-right-on-square')->url(fn (Post $r) => $r->url())->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
