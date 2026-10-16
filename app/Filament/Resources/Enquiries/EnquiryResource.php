<?php

namespace App\Filament\Resources\Enquiries;

use App\Filament\Resources\Enquiries\Pages\EditEnquiry;
use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\Enquiries\Schemas\EnquiryForm;
use App\Filament\Resources\Enquiries\Tables\EnquiriesTable;
use App\Models\Enquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EnquiryResource extends Resource
{
    protected static ?string $model = Enquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Enquiries';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EnquiryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnquiriesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false; // enquiries only come from the website
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Enquiry::where('status', 'new')->count();

        return $n ? (string) $n : null;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnquiries::route('/'),
            'edit' => EditEnquiry::route('/{record}/edit'),
        ];
    }
}
