<?php

namespace App\Filament\Resources\TrainingClasses;

use App\Filament\Resources\TrainingClasses\Pages\CreateTrainingClass;
use App\Filament\Resources\TrainingClasses\Pages\EditTrainingClass;
use App\Filament\Resources\TrainingClasses\Pages\ListTrainingClasses;
use App\Filament\Resources\TrainingClasses\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\TrainingClasses\Schemas\TrainingClassForm;
use App\Filament\Resources\TrainingClasses\Tables\TrainingClassesTable;
use App\Models\TrainingClass;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingClassResource extends Resource
{
    protected static ?string $model = TrainingClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Classes';

    protected static ?string $modelLabel = 'class';

    protected static ?string $pluralModelLabel = 'classes';

    protected static ?string $slug = 'classes';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TrainingClassForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingClassesTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $n = TrainingClass::ongoing()->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Ongoing classes';
    }

    public static function getRelations(): array
    {
        return [StudentsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingClasses::route('/'),
            'create' => CreateTrainingClass::route('/create'),
            'edit' => EditTrainingClass::route('/{record}/edit'),
        ];
    }
}
