<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CodeReviewStatus;
use App\Filament\Resources\CodeReviewResource\Pages;
use App\Filament\Resources\CodeReviewResource\Widgets\WeeklyCodeReviews;
use App\Filament\Table\Columns\WeekColumn;
use App\Filament\Table\Filters\WeekFilter;
use App\Filament\Table\Filters\YearFilter;
use App\Models\CodeReview;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\GlobalSearch\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CodeReviewResource extends Resource
{
    protected static ?string $model = CodeReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Task Tracker';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'project';

    /**
     * @param  CodeReview  $record
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $createdAt = $record->created_at;

        $week = collect([
            $createdAt?->weekOfYear,
            $createdAt?->year,
        ])->filter()->implode(' - ');

        return [
            'Week' => $week,
        ];
    }

    /**
     * @param  CodeReview  $record
     */
    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('task')
                ->label('Task/PR')
                ->button()
                ->url($record->link)
                ->openUrlInNewTab(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::getForm());
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('link')
                    ->url(fn (string $state) => $state)
                    ->formatStateUsing(fn (string $state) => parse_url($state, PHP_URL_PATH))
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                WeekColumn::make(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                WeekFilter::make(),
                YearFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->form(self::getForm())
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCodeReviews::route('/'),
        ];
    }

    /**
     * @return class-string[]
     */
    public static function getWidgets(): array
    {
        return [WeeklyCodeReviews::class];
    }

    /**
     * @return Forms\Components\Field[]
     */
    private static function getForm(): array
    {
        return [
            Forms\Components\TextInput::make('project')
                ->label('Project')
                ->required(),

            Forms\Components\TextInput::make('link')
                ->label('Link')
                ->url()
                ->required(),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->enum(CodeReviewStatus::class)
                ->options([
                    CodeReviewStatus::PENDING->value => 'Pending',
                    CodeReviewStatus::APPROVED->value => 'Approved',
                    CodeReviewStatus::REJECTED->value => 'Rejected',
                ])
                ->native(false)
                ->required()
                ->default(CodeReviewStatus::APPROVED->value),

            Forms\Components\Textarea::make('description')
                ->label('Description'),
        ];
    }
}
