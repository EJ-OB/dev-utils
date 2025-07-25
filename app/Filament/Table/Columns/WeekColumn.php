<?php

declare(strict_types=1);

namespace App\Filament\Table\Columns;

use Carbon\CarbonInterface;
use Exception;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WeekColumn extends TextColumn
{
    public static function make(string $name = 'week'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getStateUsing(function (Model $record): ?string {
            if (! $createdAtColumn = $record->getCreatedAtColumn()) {
                throw new Exception('The model is missing a "created_at" column.');
            }

            /** @var CarbonInterface|null $createdAt */
            $createdAt = $record->getAttribute($createdAtColumn);

            $state = collect([
                $createdAt?->weekOfYear,
                $createdAt?->year,
            ])->filter()->implode(' - ');

            return $createdAt ? "Week $state" : null;
        })
            ->searchable(query: function (Builder $query, string $search) {
                $query->whereRaw("concat('week ', WEEKOFYEAR(created_at), ' - ', YEAR(created_at)) like ?", [
                    '%'.strtolower($search).'%',
                ]);
            });
    }
}
