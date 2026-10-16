<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Setting;
use App\Support\PageContent;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Edits every text block and photo slot of the public pages (App\Support\PageContent).
 * The form is generated from the defaults, so any new key added there automatically gets an admin field.
 */
class PageContentEditor extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Page content';

    protected static ?string $title = 'Page content';

    protected static ?string $slug = 'page-content';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    private const LONG_KEYS = ['text', 'intro', 'lead', 'body', 'answer', 'description', 'paragraphs', 'footer_text', 'partner_text', 'reviews_text', 'sector_intro', 'plan_intro', 'proposal_intro', 'cta_text'];

    private ?array $courseOptions = null;

    public function mount(): void
    {
        $this->form->fill(collect(PageContent::PAGES)->keys()->mapWithKeys(fn ($p) => [$p => PageContent::page($p)])->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Tabs::make('Pages')->persistTabInQueryString()->tabs(
                collect(PageContent::PAGES)->map(fn ($label, $page) => Tab::make($label)
                    ->schema([Section::make()->statePath($page)->schema($this->fields(PageContent::defaults()[$page]))])
                )->values()->all()
            ),
        ]);
    }

    /** Build admin fields that mirror the shape of the default content. */
    private function fields(array $defaults): array
    {
        $fields = [];
        foreach ($defaults as $key => $value) {
            $label = Str::headline($key);

            if (is_bool($value)) {
                $fields[] = Toggle::make($key)->label($label);
            } elseif (is_int($value)) {
                $fields[] = TextInput::make($key)->label($label)->numeric()->minValue(1);
            } elseif ($key === 'popular_courses') {
                $fields[] = Select::make($key)->label($label)->multiple()->searchable()->options(fn () => $this->courses());
            } elseif (in_array($key, ['course', 'main_course'], true)) {
                $fields[] = Select::make($key)->label($label === 'Main Course' ? 'Main course' : 'Course')->searchable()->options(fn () => $this->courses());
            } elseif (is_string($value) && str_ends_with($key, 'image')) {
                $fields[] = FileUpload::make($key)->label($label)->image()->disk('public')->directory('content')
                    ->imageEditor()->maxSize(4096)->helperText('JPG/PNG/WebP up to 4 MB. Landscape photos work best.');
            } elseif (is_string($value)) {
                $long = mb_strlen($value) > 90 || in_array($key, self::LONG_KEYS, true);
                $field = $long ? Textarea::make($key)->rows(3) : TextInput::make($key);
                if ($key === 'icon') {
                    $field->helperText('Lucide icon name, e.g. award, users, terminal (lucide.dev/icons)');
                }
                $fields[] = $field->label($label);
            } elseif (is_array($value) && array_is_list($value)) {
                $first = $value[0] ?? '';
                if (is_array($first)) {
                    $titleKey = collect($first)->keys()->first(fn ($k) => in_array($k, ['title', 'label', 'question', 'name', 'course'], true));
                    $fields[] = Repeater::make($key)->label($label)->schema($this->fields($first))->columns(2)
                        ->itemLabel(fn (array $state) => $titleKey ? ($state[$titleKey] ?? null) : null)
                        ->collapsible()->collapsed()->reorderable()->cloneable();
                } else {
                    $long = in_array($key, self::LONG_KEYS, true) || mb_strlen((string) $first) > 90;
                    $fields[] = Repeater::make($key)->label($label)->simple($long ? Textarea::make('value')->rows(3)->required() : TextInput::make('value')->required())->reorderable();
                }
            } elseif (is_array($value)) {
                $fields[] = Section::make($label)->statePath($key)->schema($this->fields($value))->columns(2)->collapsible()->collapsed();
            }
        }

        return $fields;
    }

    private function courses(): array
    {
        return $this->courseOptions ??= Course::orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->slug => "{$c->code} · {$c->name}"])->all();
    }

    public function save(): void
    {
        $state = $this->form->getState();
        foreach (array_keys(PageContent::PAGES) as $page) {
            Setting::put('page_'.$page, $state[$page] ?? []);
        }
        PageContent::flush();
        Notification::make()->title('Page content saved')->body('Changes are live on the website.')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('view')->label('View website')->url('/')->openUrlInNewTab()->color('gray')];
    }
}
