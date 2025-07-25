<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeReviewResource\Widgets;

use App\Filament\Resources\CodeReviewResource\Pages\ManageCodeReviews;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeeklyCodeReviews extends BaseWidget
{
    use InteractsWithPageTable;

    /**
     * @return class-string
     */
    protected function getTablePage(): string
    {
        return ManageCodeReviews::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make(
                label: 'This Week',
                value: $this->getPageTableQuery()
                    ->whereDate('created_at', '>=', now()->startOfWeek())
                    ->whereDate('created_at', '<=', now()->endOfWeek())
                    ->count()
            ),
            Stat::make(
                label: 'Last Week',
                value: $this->getPageTableQuery()
                    ->whereDate('created_at', '>=', now()->subWeek()->startOfWeek())
                    ->whereDate('created_at', '<=', now()->subWeek()->endOfWeek())
                    ->count()
            ),
        ];
    }
}
