<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Edits the key/value `settings` table: announcement bar, contact details, social links, stats, audiences, analytics. */
class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Site settings';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    private const KEYS = ['announcement', 'contact', 'social', 'trust', 'audiences', 'analytics'];

    public function mount(): void
    {
        $this->form->fill(collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => Setting::get($k, [])])->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Tabs::make('Settings')->persistTabInQueryString()->tabs([
                Tab::make('Announcement bar')->icon('heroicon-o-megaphone')->schema([
                    Toggle::make('announcement.enabled')->label('Show the yellow offer bar'),
                    TextInput::make('announcement.text')->maxLength(140)->helperText('Keep it short: new batches, offers, events.'),
                    Grid::make(2)->schema([
                        TextInput::make('announcement.linkText')->label('Button text')->maxLength(30),
                        TextInput::make('announcement.url')->label('Button link')->placeholder('/batches/'),
                    ]),
                ]),
                Tab::make('Contact')->icon('heroicon-o-phone')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('contact.phonePrimary.label')->label('Main phone (display)')->required(),
                        TextInput::make('contact.phonePrimary.tel')->label('Main phone (dial format)')->required()->placeholder('+9779851165508'),
                        TextInput::make('contact.phoneOffice.label')->label('Office phone (display)'),
                        TextInput::make('contact.phoneOffice.tel')->label('Office phone (dial format)'),
                        TextInput::make('contact.whatsapp.number')->label('WhatsApp number (digits only, with country code)')->placeholder('9779851165508'),
                        TextInput::make('contact.email')->email()->required(),
                    ]),
                    TextInput::make('contact.address')->required(),
                    TextInput::make('contact.mapUrl')->label('Google Maps link')->url(),
                    Grid::make(2)->schema([
                        TextInput::make('contact.hours')->label('Opening hours (long)'),
                        TextInput::make('contact.hoursShort')->label('Opening hours (short)'),
                    ]),
                ]),
                Tab::make('Social')->icon('heroicon-o-share')->schema([
                    Repeater::make('social')->hiddenLabel()->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('url')->url()->required(),
                        TextInput::make('icon')->required()->helperText('facebook, linkedin, instagram'),
                    ])->columns(3)->reorderable(),
                ]),
                Tab::make('Key facts')->icon('heroicon-o-chart-bar')->schema([
                    Section::make()->description('Figures appear on the homepage and About page. Facts marked "needs verification" stay hidden until you switch on "Verified".')->schema([
                        Repeater::make('trust')->hiddenLabel()->schema([
                            TextInput::make('value')->required(),
                            TextInput::make('label')->required(),
                            TextInput::make('icon')->default('award'),
                            Textarea::make('verify')->label('Needs verification (note)')->rows(1),
                            Toggle::make('verified')->label('Verified'),
                        ])->columns(5)->reorderable(),
                    ]),
                ]),
                Tab::make('Training for')->icon('heroicon-o-users')->schema([
                    Repeater::make('audiences')->hiddenLabel()->schema([
                        Grid::make(3)->schema([
                            TextInput::make('id')->required(),
                            TextInput::make('name')->required(),
                            TextInput::make('title')->required(),
                            TextInput::make('url')->required(),
                            TextInput::make('icon')->required(),
                            TextInput::make('cta')->label('Button text')->required(),
                        ]),
                        Textarea::make('body')->rows(2),
                        Repeater::make('points')->simple(TextInput::make('text')->required()),
                    ])->itemLabel(fn (array $state) => $state['title'] ?? null)->collapsible()->reorderable(),
                ]),
                Tab::make('Analytics')->icon('heroicon-o-presentation-chart-line')->schema([
                    TextInput::make('analytics.ga4_id')->label('Google Analytics 4 measurement ID')->placeholder('G-XXXXXXXXXX')
                        ->helperText('Loaded only after the visitor accepts the cookie banner. Leave empty to disable analytics.'),
                ]),
            ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        foreach (self::KEYS as $key) {
            Setting::put($key, $state[$key] ?? []);
        }
        Notification::make()->title('Settings saved')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('view')->label('View website')->url('/')->openUrlInNewTab()->color('gray')];
    }
}
