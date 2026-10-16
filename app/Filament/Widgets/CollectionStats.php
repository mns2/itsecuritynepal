<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Students\StudentResource;
use App\Models\Payment;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Fee collection headline numbers (Fee collection page and dashboard). */
class CollectionStats extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return \App\Support\Access::allows('finance');
    }

    protected static bool $isLazy = false;

    protected static bool $isDiscovered = false; // placed explicitly

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $rs = fn (int $n) => 'Rs '.number_format($n);
        $today = (int) Payment::whereDate('paid_on', today())->sum('amount');
        $month = (int) Payment::where('paid_on', '>=', now()->startOfMonth())->sum('amount');
        $lastMonth = (int) Payment::whereBetween('paid_on', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('amount');
        $year = (int) Payment::where('paid_on', '>=', now()->startOfYear())->sum('amount');

        $owing = Student::whereIn('status', ['enrolled', 'completed'])->whereNotNull('fee_total')->get()->filter(fn (Student $s) => $s->feeBalance() > 0);
        $overdue = $owing->filter(fn (Student $s) => $s->fee_due_on && $s->fee_due_on->isPast());
        $unconfirmed = Student::where('status', 'enrolled')->where('fee_confirmed', false)->count();

        $daily = collect(range(13, 0))->map(fn ($d) => (int) Payment::whereDate('paid_on', today()->subDays($d))->sum('amount'))->all();

        return [
            Stat::make('Collected today', $rs($today))->color('success')->chart($daily)->chartColor('success')
                ->description(Payment::whereDate('paid_on', today())->count().' payments'),
            Stat::make('This month', $rs($month))->color('primary')
                ->description('Last month '.$rs($lastMonth))
                ->descriptionIcon($month >= $lastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'),
            Stat::make('This year', $rs($year))->color('info')->description(now()->year),
            Stat::make('Outstanding balances', $rs((int) $owing->sum(fn (Student $s) => $s->feeBalance())))
                ->color($overdue->isNotEmpty() ? 'danger' : 'warning')
                ->description($owing->count().' students'.($overdue->isNotEmpty() ? ' · '.$overdue->count().' overdue' : '').($unconfirmed ? " · {$unconfirmed} fee not confirmed" : ''))
                ->url(StudentResource::getUrl('index', ['filters' => ['balance_due' => ['isActive' => true]]])),
        ];
    }
}
