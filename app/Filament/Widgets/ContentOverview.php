<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Course;
use App\Models\Post;
use App\Models\Subscriber;
use App\Support\Access;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Dashboard for content and marketing roles (shown to anyone who can edit content). */
class ContentOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return Access::allows('content') || Access::allows('marketing');
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $lastPost = Post::published()->latest('published_at')->first();

        return [
            Stat::make('Published courses', Course::published()->count())->description(Course::where('is_published', false)->count().' drafts'),
            Stat::make('Blog posts', Post::published()->count())->description($lastPost ? 'Last: '.$lastPost->published_at->diffForHumans() : 'None yet'),
            Stat::make('Live campaigns', Campaign::live()->count())->color('warning')->description(Campaign::where('is_active', true)->count().' switched on'),
            Stat::make('Newsletter subscribers', Subscriber::subscribed()->count())
                ->description(Subscriber::where('status', 'pending')->count().' waiting to confirm'),
        ];
    }
}
