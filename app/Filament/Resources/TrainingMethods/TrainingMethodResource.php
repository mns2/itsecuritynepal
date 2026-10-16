<?php

namespace App\Filament\Resources\TrainingMethods;

use App\Filament\Resources\TrainingMethods\Pages\CreateTrainingMethod;
use App\Filament\Resources\TrainingMethods\Pages\EditTrainingMethod;
use App\Filament\Resources\TrainingMethods\Pages\ListTrainingMethods;
use App\Filament\Resources\TrainingMethods\Schemas\TrainingMethodForm;
use App\Filament\Resources\TrainingMethods\Tables\TrainingMethodsTable;
use App\Models\TrainingMethod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingMethodResource extends Resource
{
    use \App\Filament\Concerns\RestrictedToArea;

    protected static string $accessArea = 'catalogue';

    protected static ?string $model = TrainingMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Training methods';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TrainingMethodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingMethodsTable::configure($table);
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
            'index' => ListTrainingMethods::route('/'),
            'create' => CreateTrainingMethod::route('/create'),
            'edit' => EditTrainingMethod::route('/{record}/edit'),
        ];
    }
}
