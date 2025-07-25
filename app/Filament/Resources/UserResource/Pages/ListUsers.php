<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\SystemRoleEnum;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Exception;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('create_from_factory')
                ->label('Create from Factory')
                ->visible(fn (): bool => App::isLocal())
                ->color(Color::Fuchsia)
                ->authorize(fn (): bool => Auth::user()?->can('create', User::class) ?? false)
                ->successNotification(function (Notification $notification): Notification {
                    return $notification
                        ->title('User created from factory')
                        ->body('The user has been created from the factory.');
                })
                ->failureNotification(function (Notification $notification): Notification {
                    return $notification
                        ->title('Failed to create user from factory')
                        ->body('The user could not be created from the factory.');
                })
                ->action(function (Actions\Action $action): void {
                    try {
                        /** @var User $user */
                        $user = User::factory()->create();
                        $user->assignRole(
                            Role::whereNot('name', SystemRoleEnum::SUPER_ADMIN->value)
                                ->inRandomOrder()
                                ->firstOrFail()
                        );

                        $action->success();
                    } catch (Exception) {
                        $action->halt();
                    }
                }),
        ];
    }
}
