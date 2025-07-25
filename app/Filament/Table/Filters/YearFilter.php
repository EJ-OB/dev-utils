<?php

declare(strict_types=1);

namespace App\Filament\Table\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class YearFilter extends Filter
{
    public static function getDefaultName(): ?string
    {
        return 'year';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->form([
                TextInput::make('year')
                    ->type('number'),
            ])
            ->query(function (Builder $query, array $data) {
                $query->when($data['year'] ?? null, function (Builder $query, ?int $year) {
                    $query->whereRaw('YEAR(created_at) = ?', [$year]);
                });
            })
            ->indicateUsing(function (array $data) {
                return $data['year'] ? "Year {$data['year']}" : null;
            });
    }
}
