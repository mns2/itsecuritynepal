<?php

namespace App\Filament\Schemas;

use App\Models\SeoMeta;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * "Advanced SEO" fields. Used on its own (Settings → SEO, any page by path) and inside course, track,
 * blog, career pathway, campaign, training method and legal page editors (saved to the related SeoMeta).
 */
class SeoFields
{
    /** Section bound to the record's `seo` relationship (morphOne). */
    public static function relationshipSection(): Section
    {
        return Section::make('Advanced SEO')->icon('heroicon-o-magnifying-glass-circle')->columnSpanFull()->collapsible()->collapsed()
            ->description('Optional. Overrides the Google title and description above, sets share image, canonical, robots and custom schema (JSON-LD).')
            ->relationship('seo', condition: fn (?array $state) => self::hasValues($state))
            ->schema(self::fields());
    }

    public static function hasValues(?array $state): bool
    {
        return collect($state ?? [])->except(['path'])->filter(fn ($v) => filled($v) && $v !== [])->isNotEmpty();
    }

    public static function fields(): array
    {
        return [
            Grid::make(2)->schema([
                TextInput::make('title')->label('SEO title')->maxLength(70)->live(onBlur: true)
                    ->hint(fn (?string $state) => mb_strlen((string) $state).' / 60')->helperText('Shown as the blue link in Google. Aim for 50–60 characters.'),
                TextInput::make('focus_keyword')->label('Focus keyword')->maxLength(80)->live(onBlur: true)
                    ->placeholder('e.g. RHCSA training in Nepal')->helperText('Used only for the checklist below.'),
            ]),
            Textarea::make('description')->label('SEO description')->rows(2)->maxLength(320)->live(onBlur: true)
                ->hint(fn (?string $state) => mb_strlen((string) $state).' / 155')->helperText('Shown under the link in Google. Aim for 120–155 characters.'),
            Placeholder::make('google_preview')->label('Google preview')->content(fn (Get $get, $record) => self::preview($get, $record)),
            Placeholder::make('checklist')->label('Checklist')->content(fn (Get $get) => self::checklist($get)),

            Section::make('Social sharing (Facebook, WhatsApp, LinkedIn)')->compact()->collapsible()->collapsed()->schema([
                Grid::make(2)->schema([
                    TextInput::make('og_title')->label('Share title')->maxLength(90)->helperText('Empty = SEO title'),
                    FileUpload::make('og_image')->label('Share image')->image()->disk('public')->directory('seo')->maxSize(2048)
                        ->hintIcon('heroicon-m-photo')->hint('1200 × 630 px (1.91:1)'),
                ]),
                Textarea::make('og_description')->label('Share description')->rows(2)->maxLength(300),
            ]),

            Section::make('Indexing')->compact()->collapsible()->collapsed()->schema([
                Grid::make(2)->schema([
                    Select::make('robots')->label('Search engines')->options(SeoMeta::ROBOTS)->placeholder('Default (index, follow)')->native(false),
                    TextInput::make('canonical')->label('Canonical URL')->maxLength(500)->placeholder('Empty = this page')
                        ->helperText('Only if this page duplicates another one, e.g. https://itsecuritynepal.com/redhat/'),
                ]),
            ]),

            Repeater::make('schema')->label('Custom schema (JSON-LD)')->addActionLabel('Add schema block')
                ->helperText(new HtmlString('Extra structured data printed on the page. The site already adds Organization, Breadcrumbs, Course, FAQ (from FAQs), BlogPosting and Person automatically. Test with <a href="https://search.google.com/test/rich-results" target="_blank" rel="noopener" class="underline">Google\'s Rich Results Test</a>.'))
                ->collapsible()->itemLabel(fn (array $state) => $state['label'] ?? null)->defaultItems(0)
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('label')->label('Name (for you)')->maxLength(80)->placeholder('e.g. Event: free Linux workshop'),
                        Select::make('template')->label('Start from a template')->options(collect(self::TEMPLATES)->map(fn ($t) => $t['label'])->all())
                            ->native(false)->dehydrated(false)->live()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                if ($state && isset(self::TEMPLATES[$state])) {
                                    $set('json', json_encode(self::TEMPLATES[$state]['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                                    if (blank($get('label'))) {
                                        $set('label', self::TEMPLATES[$state]['label']);
                                    }
                                }
                            })
                            ->helperText('Fills the box below; then replace the example values.'),
                    ]),
                    CodeEditor::make('json')->label('JSON-LD')->language(Language::Json)->required()
                        ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                            $data = json_decode((string) $value, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $fail('This is not valid JSON: '.json_last_error_msg().'.');
                            } elseif (! is_array($data) || (! array_is_list($data) && empty($data['@type']))) {
                                $fail('Each schema block needs an "@type", e.g. "FAQPage" or "Event".');
                            } elseif (Str::contains((string) $value, ['<script', '</script'], ignoreCase: true)) {
                                $fail('Paste only the JSON, without <script> tags.');
                            }
                        }),
                ]),
        ];
    }

    private static function preview(Get $get, $record): HtmlString
    {
        $title = $get('title') ?: ($record?->seoable?->meta_title ?? $record?->meta_title ?? $record?->name ?? $record?->title ?? 'Page title');
        $desc = $get('description') ?: 'The description Google shows comes from the page when this is empty.';
        $path = $get('path') ?: ($record?->path ?? '/');
        $url = rtrim(config('app.url'), '/').$path;

        return new HtmlString('<div style="max-width:600px;font-family:Arial,sans-serif;padding:12px 14px;border:1px solid #DCE4F0;border-radius:8px;background:#fff">'
            .'<div style="font-size:12px;color:#4d5156">'.e(Str::limit(preg_replace('#^https?://#', '', $url), 70)).'</div>'
            .'<div style="font-size:19px;line-height:1.3;color:#1a0dab;margin:2px 0">'.e(Str::limit($title, 62)).'</div>'
            .'<div style="font-size:13px;line-height:1.45;color:#4d5156">'.e(Str::limit($desc, 158)).'</div></div>');
    }

    private static function checklist(Get $get): HtmlString
    {
        $title = (string) $get('title');
        $desc = (string) $get('description');
        $kw = Str::lower(trim((string) $get('focus_keyword')));
        $checks = [
            ['Title 30–60 characters', $title === '' ? null : (mb_strlen($title) >= 30 && mb_strlen($title) <= 60)],
            ['Description 120–155 characters', $desc === '' ? null : (mb_strlen($desc) >= 120 && mb_strlen($desc) <= 155)],
            ['Focus keyword in title', $kw === '' || $title === '' ? null : Str::contains(Str::lower($title), $kw)],
            ['Focus keyword in description', $kw === '' || $desc === '' ? null : Str::contains(Str::lower($desc), $kw)],
            ['Title starts with the keyword (ideal)', $kw === '' || $title === '' ? null : Str::startsWith(Str::lower($title), $kw)],
        ];

        return new HtmlString('<ul style="display:grid;gap:4px;font-size:13px">'.collect($checks)->map(function ($c) {
            [$label, $ok] = $c;
            $icon = $ok === null ? '○' : ($ok ? '✓' : '✕');
            $color = $ok === null ? '#5B6B82' : ($ok ? '#15803d' : '#b45309');

            return '<li style="color:'.$color.'"><strong>'.$icon.'</strong> '.e($label).'</li>';
        })->join('').'</ul>');
    }

    public const TEMPLATES = [
        'faq' => ['label' => 'FAQ (questions and answers)', 'json' => [
            '@context' => 'https://schema.org', '@type' => 'FAQPage',
            'mainEntity' => [
                ['@type' => 'Question', 'name' => 'Question 1?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Answer 1.']],
                ['@type' => 'Question', 'name' => 'Question 2?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Answer 2.']],
            ],
        ]],
        'event' => ['label' => 'Event (workshop, seminar, webinar)', 'json' => [
            '@context' => 'https://schema.org', '@type' => 'EducationEvent',
            'name' => 'Free Linux workshop', 'startDate' => '2026-11-20T14:00:00+05:45', 'endDate' => '2026-11-20T17:00:00+05:45',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode', 'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => ['@type' => 'Place', 'name' => 'IT Security Nepal', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Kathmandu', 'addressCountry' => 'NP']],
            'organizer' => ['@type' => 'EducationalOrganization', 'name' => 'IT Security Nepal', 'url' => 'https://itsecuritynepal.com/'],
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'NPR', 'availability' => 'https://schema.org/InStock', 'url' => 'https://itsecuritynepal.com/'],
            'description' => 'Short description of the event.',
        ]],
        'course_offer' => ['label' => 'Course price / offer', 'json' => [
            '@context' => 'https://schema.org', '@type' => 'Course',
            'name' => 'Course name', 'description' => 'One-sentence course description.',
            'provider' => ['@type' => 'EducationalOrganization', 'name' => 'IT Security Nepal', 'sameAs' => 'https://itsecuritynepal.com/'],
            'offers' => ['@type' => 'Offer', 'category' => 'Paid', 'price' => '0', 'priceCurrency' => 'NPR', 'validThrough' => '2026-12-31'],
            'hasCourseInstance' => ['@type' => 'CourseInstance', 'courseMode' => 'Onsite', 'courseSchedule' => ['@type' => 'Schedule', 'repeatFrequency' => 'Daily', 'startDate' => '2026-11-03']],
        ]],
        'howto' => ['label' => 'How-to (step by step guide)', 'json' => [
            '@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => 'How to ...',
            'step' => [
                ['@type' => 'HowToStep', 'name' => 'Step 1', 'text' => 'Do this first.'],
                ['@type' => 'HowToStep', 'name' => 'Step 2', 'text' => 'Then do this.'],
            ],
        ]],
        'video' => ['label' => 'Video', 'json' => [
            '@context' => 'https://schema.org', '@type' => 'VideoObject',
            'name' => 'Video title', 'description' => 'What the video shows.', 'thumbnailUrl' => 'https://itsecuritynepal.com/images/og-default.png',
            'uploadDate' => '2026-10-16', 'contentUrl' => 'https://www.youtube.com/watch?v=VIDEO_ID', 'embedUrl' => 'https://www.youtube.com/embed/VIDEO_ID',
        ]],
        'speakable' => ['label' => 'Article with speakable sections', 'json' => [
            '@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => 'Page title',
            'speakable' => ['@type' => 'SpeakableSpecification', 'cssSelector' => ['h1', '.text-lead']],
        ]],
        'local' => ['label' => 'Local business details (extra branch, hours)', 'json' => [
            '@context' => 'https://schema.org', '@type' => 'EducationalOrganization', 'name' => 'IT Security Nepal',
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Street', 'addressLocality' => 'Kathmandu', 'addressCountry' => 'NP'],
            'openingHoursSpecification' => [['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'opens' => '07:00', 'closes' => '19:00']],
        ]],
    ];
}
