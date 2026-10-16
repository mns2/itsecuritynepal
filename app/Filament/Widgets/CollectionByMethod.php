<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Student;
use Filament\Widgets\ChartWidget;

class CollectionByMethod extends ChartWidget
{
    public static function canView(): bool
    {
        return \App\Support\Access::allows('finance');
    }

    protected static bool $isLazy = false;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'By payment method';

    protected ?string $description = 'This month';

    protected ?string $maxHeight = '240px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $sums = Payment::where('paid_on', '>=', now()->startOfMonth())
            ->selectRaw('method, sum(amount) as total')->groupBy('method')->pluck('total', 'method');

        return [
            'datasets' => [[
                'data' => $sums->values()->map(fn ($v) => (int) $v)->all(),
                'backgroundColor' => ['#003275', '#F9B233', '#1F5BB8', '#5B6B82', '#8FB3E8', '#FBD38A'],
                'borderWidth' => 0,
            ]],
            'labels' => $sums->keys()->map(fn ($k) => Student::PAYMENT_METHODS[$k] ?? $k)->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['display' => false], 'y' => ['display' => false]], 'cutout' => '60%'];
    }
}
