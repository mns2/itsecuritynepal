<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class CollectionByMonth extends ChartWidget
{
    public static function canView(): bool
    {
        return \App\Support\Access::allows('finance');
    }

    protected static bool $isLazy = false;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Collected per month';

    protected ?string $description = 'Last 12 months (NPR)';

    protected ?string $maxHeight = '240px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(fn ($i) => now()->startOfMonth()->subMonthsNoOverflow($i));

        return [
            'datasets' => [[
                'label' => 'Collected',
                'data' => $months->map(fn ($m) => (int) Payment::whereBetween('paid_on', [$m, $m->copy()->endOfMonth()])->sum('amount'))->all(),
                'backgroundColor' => '#003275',
                'borderRadius' => 4,
            ]],
            'labels' => $months->map(fn ($m) => $m->format('M y'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['beginAtZero' => true]]];
    }
}
