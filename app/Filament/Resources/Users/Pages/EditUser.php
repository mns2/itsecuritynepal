<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        // Never let an admin delete their own account (and lock everyone out).
        return [DeleteAction::make()->visible(fn () => ! $this->record->is(auth()->user()))];
    }
}
