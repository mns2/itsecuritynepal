<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use App\Models\Enquiry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** Enquiries come from the website; staff only update status and notes. */
class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead')->columnSpanFull()->schema([
                Grid::make(3)->schema([
                    TextEntry::make('name'),
                    TextEntry::make('phone')->url(fn ($record) => 'tel:'.preg_replace('/[^0-9+]/', '', $record->phone))->copyable(),
                    TextEntry::make('email')->url(fn ($record) => 'mailto:'.$record->email)->copyable(),
                    TextEntry::make('audience')->label('Training for')->badge()
                        ->formatStateUsing(fn ($state, $record) => $state.($record->group_size ? " ({$record->group_size})" : '')),
                    TextEntry::make('course.name')->label('Course')->placeholder(fn ($record) => $record->interest ? ucfirst($record->interest) : '—'),
                    TextEntry::make('mode')->placeholder('—'),
                    TextEntry::make('organisation')->placeholder('—'),
                    TextEntry::make('form')->formatStateUsing(fn ($state) => Enquiry::FORMS[$state] ?? $state),
                    TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i a'),
                ]),
                TextEntry::make('message')->placeholder('No message')->columnSpanFull(),
                TextEntry::make('source_url')->label('Sent from page')->url(fn ($state) => $state)->openUrlInNewTab()->placeholder('—'),
            ]),
            Section::make('Follow-up')->columnSpanFull()->schema([
                Select::make('status')->options(Enquiry::STATUSES)->required(),
                Textarea::make('staff_notes')->rows(4)->placeholder('Calls made, batch offered, fees quoted…'),
            ]),
        ]);
    }
}
