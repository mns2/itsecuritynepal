<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use App\Models\Campaign;
use App\Models\Course;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Campaign')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('Campaign')->icon('heroicon-o-megaphone')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')->label('Campaign name')->required()->maxLength(120)->columnSpan(2)
                            ->placeholder('e.g. Dashain offer 2026')->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, Set $set, Get $get, ?Campaign $record) => $record ? null : $set('slug', Str::slug((string) $state))),
                        Toggle::make('is_active')->label('Active (show on the website)')->inline(false),
                        TextInput::make('slug')->required()->maxLength(80)->unique(ignoreRecord: true)->alphaDash()
                            ->prefix('/offer/')->suffix('/')->helperText('Landing page address.'),
                        DateTimePicker::make('starts_at')->label('Starts')->native(false)->seconds(false)->helperText('Empty = immediately'),
                        DateTimePicker::make('ends_at')->label('Ends')->native(false)->seconds(false)->after('starts_at')->helperText('Empty = until switched off'),
                        TextInput::make('priority')->numeric()->default(0)->helperText('If several campaigns are live, the highest number is shown.'),
                    ]),
                ]),
                Tab::make('Pop-up')->icon('heroicon-o-window')->schema([
                    Radio::make('style')->options(Campaign::STYLES)->default('poster')->inline()->live()->required(),
                    Grid::make(2)->schema([
                        FileUpload::make('image')->label(fn (Get $get) => $get('style') === 'poster' ? 'Poster' : 'Image (optional)')
                            ->hintIcon('heroicon-m-photo')->hint(fn (Get $get) => $get('style') === 'poster' ? '1080 × 1080 px (square) or 1200 × 800 px' : '1200 × 630 px (1.91:1)')
                            ->image()->disk('public')->directory('campaigns')->imageEditor()->maxSize(4096)
                            ->required(fn (Get $get) => $get('style') === 'poster')
                            ->helperText(fn (Get $get) => $get('style') === 'poster'
                                ? 'The whole pop-up. Square or landscape works best, e.g. 1080 × 1080 or 1200 × 800 px. Put the offer text in the image.'
                                : 'Shown above the text. Landscape, e.g. 1200 × 630 px.'),
                        FileUpload::make('mobile_image')->label('Phone version (optional)')->hintIcon('heroicon-m-photo')->hint('1080 × 1350 px (4:5 portrait)')
                            ->image()->disk('public')->directory('campaigns')->imageEditor()->maxSize(4096)
                            ->helperText('Portrait poster for phones, e.g. 1080 × 1350 px. If empty, the main image is used.')
                            ->visible(fn (Get $get) => $get('style') === 'poster'),
                    ]),
                    TextInput::make('image_alt')->label('Image description (alt text)')->maxLength(200)
                        ->required(fn (Get $get) => $get('style') === 'poster')
                        ->helperText('What the poster says, for screen readers and Google, e.g. "Dashain offer: 25% off all Red Hat courses until 30 October".'),
                    Grid::make(2)->schema([
                        TextInput::make('badge')->maxLength(40)->placeholder('e.g. Dashain offer'),
                        TextInput::make('headline')->maxLength(120)->required(fn (Get $get) => $get('style') === 'card'),
                    ])->visible(fn (Get $get) => $get('style') === 'card'),
                    Textarea::make('text')->rows(3)->maxLength(500)->visible(fn (Get $get) => $get('style') === 'card'),
                    Grid::make(3)->schema([
                        TextInput::make('offer_code')->label('Offer code (optional)')->maxLength(40)->placeholder('e.g. DASHAIN25'),
                        Toggle::make('show_countdown')->label('Show countdown to end date')->inline(false)
                            ->helperText('Needs an end date.'),
                    ]),
                    Section::make('Button')->schema([
                        Grid::make(3)->schema([
                            TextInput::make('cta_label')->label('Button text')->required()->default('Claim offer')->maxLength(40),
                            Select::make('cta_action')->label('Button opens')->options(Campaign::ACTIONS)->default('enquiry')->required()->live()->native(false),
                            TextInput::make('cta_url')->label('Link')->url()->maxLength(500)
                                ->visible(fn (Get $get) => $get('cta_action') === 'link')->required(fn (Get $get) => $get('cta_action') === 'link'),
                            Select::make('course_id')->label('Pre-select course in the form')->searchable()->native(false)
                                ->options(fn () => Course::orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->code} · {$c->name}"]))
                                ->visible(fn (Get $get) => $get('cta_action') !== 'link'),
                        ]),
                    ])->compact(),
                ]),
                Tab::make('Who sees it')->icon('heroicon-o-funnel')->schema([
                    Grid::make(3)->schema([
                        Select::make('show_on')->label('Pages')->options(Campaign::SHOW_ON)->default('all')->required()->live()->native(false),
                        Select::make('device')->options(Campaign::DEVICES)->default('all')->required()->native(false),
                        Select::make('frequency')->label('How often per visitor')->options(Campaign::FREQUENCIES)->default('day')->required()->native(false)
                            ->helperText('Closing the pop-up also counts.'),
                    ]),
                    TagsInput::make('paths')->label('Selected pages')->placeholder('/redhat/  or  /course/*')
                        ->helperText('Page addresses; * matches anything, e.g. /ethical-hacking/* for a whole track.')
                        ->visible(fn (Get $get) => $get('show_on') === 'selected')->required(fn (Get $get) => $get('show_on') === 'selected'),
                    Grid::make(3)->schema([
                        Select::make('trigger')->label('Show')->options(Campaign::TRIGGERS)->default('delay')->required()->live()->native(false),
                        TextInput::make('delay_seconds')->label('Seconds after page opens')->numeric()->minValue(0)->maxValue(120)->default(5)
                            ->visible(fn (Get $get) => $get('trigger') === 'delay'),
                        TextInput::make('scroll_percent')->label('Percent of page scrolled')->numeric()->minValue(5)->maxValue(95)->default(40)->suffix('%')
                            ->visible(fn (Get $get) => $get('trigger') === 'scroll'),
                    ]),
                    Section::make()->schema([])->description('The pop-up is never shown in the admin panel, never while the enquiry form is open, and never again to someone who already sent an enquiry from it.'),
                ]),
                Tab::make('Landing page')->icon('heroicon-o-document-text')->schema([
                    Toggle::make('has_landing_page')->label('Create a landing page at /offer/…/')->default(true)->live()
                        ->helperText('A shareable page for Facebook / Instagram ads: poster, details, countdown and enquiry form.'),
                    MarkdownEditor::make('landing_body')->label('Details (terms, eligible courses, deadline…)')
                        ->toolbarButtons([['bold', 'italic', 'link'], ['heading', 'bulletList', 'orderedList'], ['undo', 'redo']])
                        ->visible(fn (Get $get) => $get('has_landing_page')),
                    Grid::make(2)->schema([
                        TextInput::make('meta_title')->label('Google title')->maxLength(70)->helperText('Empty = headline or campaign name'),
                        Textarea::make('meta_description')->label('Google description')->rows(2)->maxLength(320),
                    ])->visible(fn (Get $get) => $get('has_landing_page')),
                ]),
            ]),
            \App\Filament\Schemas\SeoFields::relationshipSection(),
        ]);
    }
}
