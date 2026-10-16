<?php

namespace App\Filament\Resources\SeoMetas;

use App\Filament\Resources\SeoMetas\Pages\CreateSeoMeta;
use App\Filament\Resources\SeoMetas\Pages\EditSeoMeta;
use App\Filament\Resources\SeoMetas\Pages\ListSeoMetas;
use App\Filament\Schemas\SeoFields;
use App\Http\Controllers\SeoController;
use App\Models\SeoMeta;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Advanced SEO for any public page by address (home, about, listings…), plus an overview of all SEO records. */
class SeoMetaResource extends Resource
{
    use \App\Filament\Concerns\RestrictedToArea;

    protected static string $accessArea = 'seo';

    protected static ?string $model = SeoMeta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'SEO (all pages)';

    protected static ?string $modelLabel = 'SEO settings';

    protected static ?string $pluralModelLabel = 'SEO settings';

    protected static ?string $slug = 'seo';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page')->columnSpanFull()->schema([
                Select::make('path')->label('Page')->required()->searchable()->native(false)
                    ->options(fn () => SeoController::urls()->mapWithKeys(fn ($u) => [$u[0] => $u[0]])->all())
                    ->getSearchResultsUsing(fn (string $search) => SeoController::urls()->pluck(0)->filter(fn ($p) => str_contains($p, $search))
                        ->push('/'.trim($search, '/').'/')->unique()->mapWithKeys(fn ($p) => [$p => $p])->all())
                    ->disabled(fn (?SeoMeta $record) => (bool) $record?->seoable_id)
                    ->helperText(fn (?SeoMeta $record) => $record?->seoable_id
                        ? 'Linked to '.class_basename($record->seoable_type).': the address follows it automatically.'
                        : 'Pick a page, or type an address such as /about/. Courses, tracks, blog posts and pathways can also be edited from their own editor.')
                    ->live(),
            ]),
            Section::make('SEO')->columnSpanFull()->schema(SeoFields::fields()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('path')
            ->columns([
                TextColumn::make('path')->label('Page')->searchable()->url(fn (SeoMeta $r) => url($r->path), shouldOpenInNewTab: true)
                    ->description(fn (SeoMeta $r) => $r->seoable_type ? class_basename($r->seoable_type) : 'Page'),
                TextColumn::make('title')->label('SEO title')->limit(50)->placeholder('Default')->wrap(),
                TextColumn::make('robots')->badge()->placeholder('index')->color(fn ($state) => str_starts_with((string) $state, 'noindex') ? 'danger' : 'gray'),
                TextColumn::make('schema')->label('Schema blocks')->alignCenter()->state(fn (SeoMeta $r) => count($r->schema ?? []) ?: '—'),
                IconColumn::make('og_image')->label('Share image')->boolean()->state(fn (SeoMeta $r) => filled($r->og_image))->alignCenter(),
                TextColumn::make('updated_at')->label('Updated')->since()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('robots')->options(SeoMeta::ROBOTS),
            ])
            ->recordActions([
                Action::make('test')->label('Rich results test')->icon('heroicon-o-beaker')->color('gray')
                    ->url(fn (SeoMeta $r) => 'https://search.google.com/test/rich-results?url='.urlencode(rtrim(config('app.url'), '/').$r->path), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('No custom SEO yet')
            ->emptyStateDescription('Every page already has a title, description, canonical link and automatic schema. Add custom SEO here only where you want to change them.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoMetas::route('/'),
            'create' => CreateSeoMeta::route('/create'),
            'edit' => EditSeoMeta::route('/{record}/edit'),
        ];
    }
}
