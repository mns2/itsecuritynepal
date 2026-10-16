<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\TrainingClasses\TrainingClassResource;
use App\Models\Enquiry;
use App\Models\Student;
use App\Models\TrainingClass;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/** Dashboard headline numbers: classes, students, completions, leads. */
class EnquiryStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '60s';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();
        $leads = Enquiry::where('status', '!=', 'spam');

        $ongoing = TrainingClass::ongoing()->count();
        $startingSoon = TrainingClass::upcoming()->whereBetween('starts_on', [today(), today()->addDays(30)])->count();

        $studying = Student::studying()->count();
        $enrolledThisMonth = Student::where('enrolled_on', '>=', $monthStart)->count();

        $completed = Student::completed()->count();
        $completedThisYear = Student::completed()->where('completed_on', '>=', now()->startOfYear())->count();
        $certified = Student::completed()->where('certified', true)->count();

        $new = Enquiry::where('status', 'new')->count();
        $leadsThisMonth = (clone $leads)->where('created_at', '>=', $monthStart)->count();
        $recent = (clone $leads)->where('created_at', '>=', now()->subDays(90));
        $recentTotal = (clone $recent)->count();
        $conversion = $recentTotal ? round((clone $recent)->where('status', 'enrolled')->count() / $recentTotal * 100) : null;

        return [
            Stat::make('Ongoing classes', $ongoing)
                ->description($startingSoon ? "{$startingSoon} starting in the next 30 days" : 'No classes starting in the next 30 days')
                ->descriptionIcon('heroicon-m-presentation-chart-bar')->color('primary')
                ->url(TrainingClassResource::getUrl('index', ['filters' => ['status' => ['value' => 'ongoing']]])),
            Stat::make('Students studying now', $studying)
                ->description("{$enrolledThisMonth} enrolled this month")->descriptionIcon('heroicon-m-user-plus')->color('info')
                ->chart($this->weekly(Student::query(), 'enrolled_on'))->chartColor('info')
                ->url(StudentResource::getUrl('index', ['filters' => ['status' => ['value' => 'enrolled']]])),
            Stat::make('Students completed', $completed)
                ->description("{$completedThisYear} this year · {$certified} certified")->descriptionIcon('heroicon-m-check-badge')->color('success')
                ->url(StudentResource::getUrl('index', ['filters' => ['status' => ['value' => 'completed']]])),
            Stat::make('New leads', $new)
                ->description($leadsThisMonth.' this month'.($conversion !== null ? " · {$conversion}% enrolled (90 days)" : ''))
                ->descriptionIcon('heroicon-m-inbox-arrow-down')->color($new ? 'warning' : 'success')
                ->chart($this->weekly(Enquiry::where('status', '!=', 'spam'), 'created_at'))->chartColor('warning')
                ->url(EnquiryResource::getUrl('index', ['filters' => ['status' => ['value' => 'new']]])),
        ];
    }

    /** Counts per week for the last 8 weeks (sparkline). */
    private function weekly($query, string $column): array
    {
        $from = now()->startOfWeek()->subWeeks(7);
        $dates = $query->where($column, '>=', $from)->pluck($column);

        return collect(range(0, 7))->map(function ($i) use ($from, $dates) {
            $start = $from->copy()->addWeeks($i);

            return $dates->filter(fn ($d) => Carbon::parse($d)->between($start, $start->copy()->endOfWeek()))->count();
        })->all();
    }
}
