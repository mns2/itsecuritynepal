<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Course;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Article')->columnSpanFull()->schema([
                TextInput::make('title')->required()->maxLength(180)->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set, ?string $old, $get) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                Grid::make(3)->schema([
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->helperText('URL: /slug/'),
                    Select::make('category')->options(['Career guides' => 'Career guides', 'Certification guides' => 'Certification guides', 'Programmes' => 'Programmes', 'News & offers' => 'News & offers'])->required(),
                    DateTimePicker::make('published_at')->label('Publish at')->native(false)->helperText('Empty = draft'),
                ]),
                Textarea::make('excerpt')->rows(2)->maxLength(300),
                MarkdownEditor::make('body')->required()->columnSpanFull()
                    ->toolbarButtons(['heading', 'bold', 'italic', 'link', 'bulletList', 'orderedList', 'blockquote', 'table', 'undo', 'redo']),
            ]),
            Section::make('Media, related courses & SEO')->columnSpanFull()->collapsed()->schema([
                FileUpload::make('featured_image')->image()->disk('public')->directory('blog')->imageEditor()->maxSize(2048)
                    ->helperText('16:9, at least 1200 × 675 px. Shown on the article and in social shares.'),
                Select::make('related_course_ids')->label('Related courses')->multiple()->searchable()
                    ->options(fn () => Course::orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->code} · {$c->name}"])),
                TextInput::make('author')->default('IT Security Nepal team'),
                TextInput::make('meta_title')->maxLength(70),
                Textarea::make('meta_description')->rows(2)->maxLength(320),
            ]),
        ]);
    }
}
