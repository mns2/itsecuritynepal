<?php

namespace App\Filament\Resources\TrainingClasses\RelationManagers;

use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Tables\StudentsTable;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Students in this class';

    public function form(Schema $schema): Schema
    {
        return $schema->components(StudentForm::fields(inClass: true));
    }

    public function table(Table $table): Table
    {
        return StudentsTable::configure($table, inClass: true)
            ->headerActions([
                CreateAction::make()->label('Add student')->mutateDataUsing(function (array $data) {
                    $class = $this->getOwnerRecord();

                    return $data + ['course_id' => $class->course_id, 'audience' => $class->audience, 'organisation' => $class->organisation];
                }),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('complete')->label('Mark completed')->icon('heroicon-o-check-badge')->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['status' => 'completed', 'completed_on' => now()])),
                DeleteBulkAction::make(),
            ])]);
    }
}
