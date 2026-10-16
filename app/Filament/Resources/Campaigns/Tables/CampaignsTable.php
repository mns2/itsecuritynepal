<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Models\Campaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount(['enquiries as leads_count' => fn ($q) => $q->where('status', '!=', 'spam')]))
            ->columns([
                ImageColumn::make('image')->disk('public')->label('')->imageHeight(44)->square(),
                TextColumn::make('name')->searchable()->description(fn (Campaign $r) => $r->landingUrl()),
                TextColumn::make('state')->label('Status')->badge()
                    ->state(fn (Campaign $r) => match (true) {
                        ! $r->is_active => 'Off',
                        $r->starts_at && $r->starts_at->isFuture() => 'Scheduled',
                        $r->ends_at && $r->ends_at->isPast() => 'Ended',
                        default => 'Live',
                    })
                    ->color(fn ($state) => match ($state) { 'Live' => 'success', 'Scheduled' => 'warning', default => 'gray' }),
                TextColumn::make('ends_at')->label('Runs')->date('j M Y')->placeholder('No end date')
                    ->description(fn (Campaign $r) => $r->starts_at ? 'from '.$r->starts_at->format('j M') : null),
                TextColumn::make('views')->numeric()->alignEnd()->sortable(),
                TextColumn::make('clicks')->numeric()->alignEnd()->sortable()
                    ->description(fn (Campaign $r) => $r->views ? round($r->clicks / $r->views * 100, 1).'% of views' : null),
                TextColumn::make('leads_count')->label('Leads')->numeric()->alignEnd()->sortable()
                    ->url(fn (Campaign $r) => route('filament.admin.resources.enquiries.index', ['filters' => ['campaign' => ['value' => $r->id]]])),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([
                Action::make('preview')->label('Preview')->icon('heroicon-o-eye')->color('gray')
                    ->url(fn (Campaign $r) => url('/').'?campaign_preview='.$r->id, shouldOpenInNewTab: true),
                Action::make('landing')->label('Landing page')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->visible(fn (Campaign $r) => $r->has_landing_page)
                    ->url(fn (Campaign $r) => url($r->landingUrl()).'?campaign_preview='.$r->id, shouldOpenInNewTab: true),
                ReplicateAction::make()->label('Copy')
                    ->excludeAttributes(['views', 'clicks', 'leads_count'])
                    ->mutateRecordDataUsing(fn (array $data) => array_merge($data, ['name' => $data['name'].' (copy)', 'slug' => $data['slug'].'-copy-'.now()->format('His'), 'is_active' => false])),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No campaigns yet')
            ->emptyStateDescription('Create a campaign to show an offer pop-up on the website, with its own landing page for ads.');
    }
}
