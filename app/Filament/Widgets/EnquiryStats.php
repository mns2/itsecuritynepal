<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enquiry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnquiryStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $new = Enquiry::where('status', 'new')->count();

        return [
            Stat::make('New enquiries', $new)->description('Waiting for a call back')->color($new ? 'warning' : 'success')
                ->url(route('filament.admin.resources.enquiries.index')),
            Stat::make('Enquiries this month', Enquiry::where('status', '!=', 'spam')->where('created_at', '>=', now()->startOfMonth())->count())
                ->description(Enquiry::where('audience', 'Group')->where('created_at', '>=', now()->startOfMonth())->count().' group enquiries'),
            Stat::make('Published courses', Course::published()->count()),
        ];
    }
}
