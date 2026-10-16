<?php

namespace App\Filament\Trainer\Resources\MyClasses\Pages;

use App\Filament\Trainer\Resources\MyClasses\MyClassResource;
use Filament\Resources\Pages\ListRecords;

class ListMyClasses extends ListRecords
{
    protected static string $resource = MyClassResource::class;
}
