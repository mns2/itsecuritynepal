<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use Filament\Widgets\ChartWidget;

class LeadsByAudience extends ChartWidget
{
    public static function canView(): bool
    {
        return \App\Support\Access::allows('leads');
    }

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected ?string $heading = 'Leads by training type';

    protected ?string $description = 'Last 90 days';

    protected ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Enquiry::where('status', '!=', 'spam')->where('created_at', '>=', now()->subDays(90))
            ->selectRaw('audience, count(*) as n')->groupBy('audience')->pluck('n', 'audience');
        // corporate-page enquiries count as corporate batch leads
        $data = [
            'Individual' => (int) ($counts['Individual'] ?? 0),
            'Group' => (int) ($counts['Group'] ?? 0),
            'Corporate Batch' => (int) (($counts['Corporate Batch'] ?? 0) + ($counts['Corporate'] ?? 0)),
        ];

        return [
            'datasets' => [[
                'data' => array_values($data),
                'backgroundColor' => ['#003275', '#F9B233', '#1F5BB8'],
                'borderWidth' => 0,
            ]],
            'labels' => array_keys($data),
        ];
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['display' => false], 'y' => ['display' => false]], 'cutout' => '62%'];
    }
}
