<?php

namespace App\Filament\Pages;

use App\Models\Campaign;
use App\Models\Setting;
use App\Support\GoogleReviews;
use App\Support\Popups;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Illuminate\Support\Facades\Cache;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Edits the key/value `settings` table: announcement bar, contact details, social links, stats, audiences, analytics. */
class SiteSettings extends Page
{
    public static function canAccess(): bool
    {
        return \App\Support\Access::allows('settings');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Site settings';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    private const KEYS = ['announcement', 'contact', 'social', 'trust', 'audiences', 'analytics', 'popups', 'reviews', 'exam_booking'];

    public function mount(): void
    {
        $this->form->fill(collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => match ($k) { 'popups' => Popups::settings(), 'reviews' => GoogleReviews::settings(), 'exam_booking' => \App\Models\ExamBooking::settings(), default => Setting::get($k, []) }])->all());
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
                Tab::make('Pop-ups')->icon('heroicon-o-window')->schema([
                    Section::make()->description('Only one pop-up is shown per page view. When several are switched on, a live campaign comes first, then the Course Finder, then the newsletter. Pop-ups never appear over the enquiry form or in the admin panel.')->schema([]),
                    Section::make('Marketing offers and campaigns')->schema([
                        Toggle::make('popups.campaigns_enabled')->label('Show campaign pop-ups on the website')
                            ->helperText('Master switch. Each campaign also has its own Active switch (Leads → Campaigns). Landing pages (/offer/…/) keep working.'),
                    ])->compact(),
                    Section::make('Course Finder pop-up')->schema([
                        Toggle::make('popups.course_finder.enabled')->label('Show the Course Finder pop-up')->live(),
                        Grid::make(4)->schema([
                            TextInput::make('popups.course_finder.delay_seconds')->label('Seconds after opening')->numeric()->minValue(0)->maxValue(120)->required(),
                            Select::make('popups.course_finder.frequency')->label('How often')->options(Popups::FREQUENCIES)->native(false)->required(),
                            Select::make('popups.course_finder.show_on')->label('Pages')->options(['all' => 'Every page', 'home' => 'Homepage only'])->native(false)->required(),
                            Select::make('popups.course_finder.device')->label('Devices')->options(Campaign::DEVICES)->native(false)->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('popups.course_finder.badge')->maxLength(40),
                            TextInput::make('popups.course_finder.button')->label('Button text')->maxLength(40)->required(),
                        ]),
                        TextInput::make('popups.course_finder.title')->maxLength(120)->required(),
                        Textarea::make('popups.course_finder.text')->rows(2)->maxLength(300),
                        FileUpload::make('popups.course_finder.image')->label('Image (optional)')->hintIcon('heroicon-m-photo')->hint('1280 × 720 px (16:9)')->image()->disk('public')->directory('popups')->maxSize(4096),
                    ])->compact()->collapsible(),
                    Section::make('Newsletter and offers sign-up')->schema([
                        Grid::make(3)->schema([
                            Toggle::make('popups.newsletter.enabled')->label('Show the newsletter pop-up')->live(),
                            Toggle::make('popups.newsletter.footer_enabled')->label('Show the sign-up box in the footer'),
                            Toggle::make('popups.newsletter.double_opt_in')->label('Ask new subscribers to confirm by email (recommended)')
                                ->helperText('Keeps fake and mistyped addresses off the list. Needs working email (SMTP).'),
                        ]),
                        Grid::make(4)->schema([
                            Select::make('popups.newsletter.trigger')->label('Show')->options(Campaign::TRIGGERS)->native(false)->required()->live(),
                            TextInput::make('popups.newsletter.delay_seconds')->label('Seconds after opening')->numeric()->minValue(0)->maxValue(300)
                                ->visible(fn (Get $get) => $get('popups.newsletter.trigger') === 'delay'),
                            TextInput::make('popups.newsletter.scroll_percent')->label('Percent scrolled')->numeric()->minValue(5)->maxValue(95)->suffix('%')
                                ->visible(fn (Get $get) => $get('popups.newsletter.trigger') === 'scroll'),
                            Select::make('popups.newsletter.frequency')->label('How often')->options(Popups::FREQUENCIES)->native(false)->required(),
                            Select::make('popups.newsletter.device')->label('Devices')->options(Campaign::DEVICES)->native(false)->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('popups.newsletter.badge')->maxLength(40),
                            TextInput::make('popups.newsletter.button')->label('Button text')->maxLength(40)->required(),
                        ]),
                        TextInput::make('popups.newsletter.per_minute')->label('Email campaigns: emails per minute')->numeric()->minValue(1)->maxValue(300)->required()
                            ->helperText('Keep under your hosting email limit. Example: a limit of 500 per hour means at most 8 per minute.'),
                        TextInput::make('popups.newsletter.title')->maxLength(120)->required(),
                        Textarea::make('popups.newsletter.text')->rows(2)->maxLength(300),
                    ])->compact()->collapsible(),
                ]),
                Tab::make('Google reviews')->icon('heroicon-o-star')->schema([
                    Section::make()->description(new \Illuminate\Support\HtmlString(
                        'Shows your real Google rating and up to 5 recent reviews from your Google Business Profile. '
                        .'Needs <code>GOOGLE_PLACES_API_KEY</code> in the server\'s .env '.(filled(config('services.google.places_key')) ? '(<strong>set</strong>)' : '(<strong>not set yet</strong>)')
                        .' and your Place ID: find it at <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener" class="underline">Google\'s Place ID finder</a>.'
                    ))->schema([
                        Toggle::make('reviews.enabled')->label('Show Google reviews on the website'),
                        Grid::make(2)->schema([
                            TextInput::make('reviews.place_id')->label('Google Place ID')->placeholder('ChIJ…')->maxLength(200),
                            TextInput::make('reviews.title')->label('Section heading')->maxLength(100),
                        ]),
                        Grid::make(4)->schema([
                            Select::make('reviews.min_rating')->label('Show reviews with at least')->native(false)
                                ->options([0 => 'All ratings', 3 => '3 stars', 4 => '4 stars', 5 => '5 stars'])
                                ->helperText('The overall rating and count always show.'),
                            Toggle::make('reviews.show_home')->label('Homepage')->inline(false),
                            Toggle::make('reviews.show_about')->label('About page')->inline(false),
                            Toggle::make('reviews.show_course')->label('Course pages')->inline(false),
                        ]),
                        Actions::make([
                            Action::make('testReviews')->label('Test connection and refresh')->icon('heroicon-o-arrow-path')->color('gray')
                                ->action(function () {
                                    $this->save(silent: true);
                                    Cache::forget('google.reviews');
                                    $error = null;
                                    $data = GoogleReviews::configured() ? GoogleReviews::fetch($error) : null;
                                    if ($data) {
                                        Cache::put('google.reviews', $data, now()->addHours(12));
                                        Notification::make()->success()->title('Connected to Google')
                                            ->body("Rating {$data['rating']} from {$data['count']} reviews; ".count($data['reviews']).' review texts received.')->send();
                                    } else {
                                        Notification::make()->danger()->title('Could not load reviews')
                                            ->body(GoogleReviews::configured() ? ($error ?: 'Unknown error') : 'Switch it on, enter the Place ID and set GOOGLE_PLACES_API_KEY in .env first.')->send();
                                    }
                                }),
                        ]),
                    ]),
                ]),
                // Admin-only on purpose: whoever can change the payment QR controls where candidates send money.
                Tab::make('Exam booking')->icon('heroicon-o-ticket')->schema([
                    Toggle::make('exam_booking.enabled')->label('Accept online Red Hat exam bookings')
                        ->helperText('The form at /red-hat-exam-booking-nepal/ opens only when this is on AND a payment QR code is uploaded.'),
                    Grid::make(2)->schema([
                        TextInput::make('exam_booking.fee_npr')->label('Booking fee (NPR, per exam)')->numeric()->minValue(1)->required(),
                        TextInput::make('exam_booking.payee_note')->label('Text under the QR (optional)')->maxLength(120)
                            ->helperText('e.g. the account name exactly as it appears when scanning. Leave empty if unsure.'),
                    ]),
                    FileUpload::make('exam_booking.qr_image')->label('Official payment QR code')->image()->disk('public')->directory('payment')
                        ->maxSize(2048)->imagePreviewHeight('220')->hintIcon('heroicon-m-qr-code')->hint('Original PNG/JPG from the bank or wallet app, at least 600 × 600 px')
                        ->helperText('Upload the original file (not a photo of a printout). Scan it with a phone after saving to check it opens the right account.'),
                    Repeater::make('exam_booking.exams')->label('Exams on the form')->reorderable()->defaultItems(0)->columns(2)
                        ->schema([
                            TextInput::make('name')->required()->maxLength(120)->placeholder('RHCSA: Red Hat Certified System Administrator'),
                            TextInput::make('code')->label('Exam code (optional)')->maxLength(20)->placeholder('EX200'),
                        ])
                        ->helperText('Changing a name does not change bookings already received.'),
                ]),
                Tab::make('Analytics')->icon('heroicon-o-presentation-chart-line')->schema([
                    TextInput::make('analytics.ga4_id')->label('Google Analytics 4 measurement ID')->placeholder('G-XXXXXXXXXX')
                        ->helperText('Leave empty to disable analytics.'),
                    Toggle::make('analytics.require_consent')->label('Ask visitors before loading analytics (cookie banner)')
                        ->helperText('Off: analytics loads for everyone without a banner. Visitors in the EU, UK and Switzerland are counted without analytics cookies (Google Consent Mode), ads features stay off, and a footer "Analytics opt-out" link lets anyone opt out. On: nothing loads until the visitor clicks Accept.'),
                ]),
            ]),
        ]);
    }

    public function save(bool $silent = false): void
    {
        $state = $this->form->getState();
        foreach (self::KEYS as $key) {
            Setting::put($key, $state[$key] ?? []);
        }
        Cache::forget('google.reviews'); // Place ID or switch may have changed
        if (! $silent) {
            Notification::make()->title('Settings saved')->success()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('view')->label('View website')->url('/')->openUrlInNewTab()->color('gray')];
    }
}
