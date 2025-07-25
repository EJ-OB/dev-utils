<?php

declare(strict_types=1);

namespace App\Filament\Table\Filters;

use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class WeekFilter extends Filter
{
    public static function getDefaultName(): ?string
    {
        return 'week';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->form([
                Select::make('week')
                    ->native(false)
                    ->searchable()
                    ->optionsLimit(53)
                    ->options($this->options()),
            ])
            ->query(function (Builder $query, array $data) {
                $query->when($data['week'] ?? null, function (Builder $query, ?int $week) {
                    $query->whereRaw('WEEKOFYEAR(created_at) = ?', [$week]);
                });
            })
            ->indicateUsing(function (array $data) {
                return $data['week'] ? "Week {$data['week']}" : null;
            });
    }

    /**
     * @return array<string, string>
     */
    protected function options(): array
    {
        $weeks = [];

        foreach (range(1, 53) as $ii) {
            $weeks[(string) $ii] = (string) $ii;
        }

        return array_combine($weeks, $weeks);
    }
}
