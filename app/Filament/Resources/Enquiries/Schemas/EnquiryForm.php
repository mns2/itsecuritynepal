<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use App\Models\Course;
use App\Models\Enquiry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * Website enquiries are read-only (staff update status and notes).
 * Walk-in / phone / social enquiries are typed in by staff ("New enquiry") and stay editable.
 */
class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead')->columnSpanFull()
                ->visible(fn (string $operation, ?Enquiry $record) => $operation === 'create' || self::isManual($record))
                ->schema(self::inputs()),
            Section::make('Lead')->columnSpanFull()
                ->visible(fn (string $operation, ?Enquiry $record) => $operation !== 'create' && ! self::isManual($record))
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('name'),
                        TextEntry::make('phone')->url(fn ($record) => 'tel:'.preg_replace('/[^0-9+]/', '', $record->phone))->copyable(),
                        TextEntry::make('email')->url(fn ($record) => 'mailto:'.$record->email)->copyable(),
                        TextEntry::make('audience')->label('Training for')->badge()
                            ->formatStateUsing(fn ($state, $record) => $state.($record->group_size ? " ({$record->group_size})" : '')),
                        TextEntry::make('course.name')->label('Course')->placeholder(fn ($record) => $record->interest ? ucfirst($record->interest) : '—'),
                        TextEntry::make('mode')->placeholder('—'),
                        TextEntry::make('organisation')->placeholder('—'),
                        TextEntry::make('form')->label('Source')->formatStateUsing(fn ($state) => Enquiry::FORMS[$state] ?? $state),
                        TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i a'),
                    ]),
                    TextEntry::make('message')->placeholder('No message')->columnSpanFull(),
                    TextEntry::make('campaign.name')->label('From campaign')->badge()->color('warning')->placeholder('—'),
                    TextEntry::make('source_url')->label('Sent from page')->url(fn ($state) => $state)->openUrlInNewTab()->placeholder('—'),
                ]),
            Section::make('Follow-up')->columnSpanFull()->schema([
                Select::make('status')->options(Enquiry::STATUSES)->default('new')->required(),
                Textarea::make('staff_notes')->rows(4)->placeholder('Calls made, batch offered, fees quoted…'),
            ]),
        ]);
    }

    public static function isManual(?Enquiry $record): bool
    {
        return $record && array_key_exists($record->form, Enquiry::MANUAL_SOURCES);
    }

    private static function inputs(): array
    {
        return [
            Grid::make(3)->schema([
                Select::make('form')->label('Came in via')->options(Enquiry::MANUAL_SOURCES)->default('walkin')->required()->native(false),
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('phone')->tel()->required()->maxLength(30),
                TextInput::make('email')->email()->maxLength(160)->helperText('Optional for walk-ins and calls.'),
                Select::make('course_id')->label('Course')->searchable()->native(false)
                    ->options(fn () => Course::orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->code} · {$c->name}"])),
                TextInput::make('interest')->label('Or interest (if no course yet)')->maxLength(120),
                Select::make('audience')->label('Training for')->options(array_combine(Enquiry::AUDIENCES, Enquiry::AUDIENCES))->default('Individual')->required()->live()->native(false),
                TextInput::make('group_size')->label('Group size / staff')->maxLength(40)->visible(fn (Get $get) => $get('audience') !== 'Individual'),
                TextInput::make('organisation')->maxLength(160)->visible(fn (Get $get) => $get('audience') === 'Corporate Batch'),
                Select::make('mode')->options(array_combine(Course::MODES, Course::MODES))->native(false),
                Select::make('campaign_id')->label('Campaign (if they mentioned an offer)')->relationship('campaign', 'name')->native(false),
            ]),
            Textarea::make('message')->label('What they asked about')->rows(3),
        ];
    }
}
