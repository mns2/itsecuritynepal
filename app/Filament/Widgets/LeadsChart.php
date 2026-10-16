<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use App\Models\Student;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class LeadsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Leads and enrolments';

    protected ?string $description = 'Per week, last 12 weeks';

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $from = now()->startOfWeek()->subWeeks(11);
        $weeks = collect(range(0, 11))->map(fn ($i) => $from->copy()->addWeeks($i));
        $count = function ($dates) use ($weeks) {
            return $weeks->map(fn ($w) => $dates->filter(fn ($d) => Carbon::parse($d)->between($w, $w->copy()->endOfWeek()))->count())->all();
        };

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $count(Enquiry::where('status', '!=', 'spam')->where('created_at', '>=', $from)->pluck('created_at')),
                    'borderColor' => '#F9B233', 'backgroundColor' => 'rgba(249,178,51,.18)', 'fill' => true, 'tension' => 0.3,
                ],
                [
                    'label' => 'Enrolments',
                    'data' => $count(Student::where('enrolled_on', '>=', $from)->pluck('enrolled_on')),
                    'borderColor' => '#003275', 'backgroundColor' => 'rgba(0,50,117,.08)', 'fill' => true, 'tension' => 0.3,
                ],
            ],
            'labels' => $weeks->map(fn ($w) => $w->format('j M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]], 'plugins' => ['legend' => ['position' => 'bottom']]];
    }
}
