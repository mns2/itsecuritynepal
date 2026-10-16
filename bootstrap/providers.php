<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\TrainerPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    TrainerPanelProvider::class,
];
