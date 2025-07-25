<?php

declare(strict_types=1);

namespace App\Filament;

use Filament\Tables;

class FilamentConfigurations
{
    public function boot(): void
    {
        $this->tableConfigurations();
    }

    protected function tableConfigurations(): void
    {
        Tables\Actions\Action::configureUsing(function (Tables\Actions\Action $action): void {
            $action->hiddenLabel();

            match ($action::class) {
                Tables\Actions\AssociateAction::class => $action->tooltip('Associate'),
                Tables\Actions\AttachAction::class => $action->tooltip('Attach'),
                Tables\Actions\CreateAction::class => $action->tooltip('Create'),
                Tables\Actions\DeleteAction::class => $action->tooltip('Delete'),
                Tables\Actions\DetachAction::class => $action->tooltip('Detach'),
                Tables\Actions\DissociateAction::class => $action->tooltip('Dissociate'),
                Tables\Actions\EditAction::class => $action->tooltip('Edit'),
                Tables\Actions\ExportAction::class => $action->tooltip('Export'),
                Tables\Actions\ForceDeleteAction::class => $action->tooltip('Force Delete'),
                Tables\Actions\ImportAction::class => $action->tooltip('Import'),
                Tables\Actions\ReplicateAction::class => $action->tooltip('Replicate'),
                Tables\Actions\RestoreAction::class => $action->tooltip('Restore'),
                Tables\Actions\SelectAction::class => $action->tooltip('Select'),
                Tables\Actions\ViewAction::class => $action->tooltip('View'),
                default => $action->tooltip(null),
            };
        }, isImportant: true);

        Tables\Columns\Column::configureUsing(function (Tables\Columns\Column $column): void {
            $column
                ->toggleable()
                ->searchable();
        });
    }
}
