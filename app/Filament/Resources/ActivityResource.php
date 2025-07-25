<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'fluentui-history-48-o';

    protected static ?string $navigationGroup = 'System Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Activity Log';

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make([
                    TextEntry::make('event')
                        ->inlineLabel()
                        ->badge(),

                    TextEntry::make('subject_type')
                        ->label('Subject')
                        ->inlineLabel()
                        ->badge()
                        ->formatStateUsing(static fn (Activity $activity): string => Str::afterLast($activity->subject_type ?? '', '\\')),

                    TextEntry::make('causer.name')
                        ->inlineLabel()
                        ->badge(),

                    Grid::make()->schema([
                        TextEntry::make('created_at')
                            ->inlineLabel()
                            ->badge(),

                        TextEntry::make('updated_at')
                            ->inlineLabel()
                            ->badge(),
                    ]),
                ])->columns(),

                KeyValueEntry::make('properties.old')
                    ->label('Old')
                    ->columnSpanFull(),

                KeyValueEntry::make('properties.attributes')
                    ->label('New')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event')
                    ->toggleable(false)
                    ->badge(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->toggleable(false)
                    ->label('Subject')
                    ->formatStateUsing(static fn (Activity $activity): string => Str::afterLast($activity->subject_type ?? '', '\\')),

                Tables\Columns\TextColumn::make('causer.name')
                    ->toggleable(false)
                    ->getStateUsing(static fn (Activity $activity): string => $activity->causer->name ?? 'System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->date('Y-m-d H:i:s'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->date('Y-m-d H:i:s'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('view')
                    ->label('')
                    ->tooltip('View Changes')
                    ->icon('heroicon-o-eye'),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['causer', 'subject']);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageActivities::route('/'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getPermissionPrefixes(): array
    {
        return [];
    }
}
