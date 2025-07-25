<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

use Closure;
use Exception;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmPassword;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Livewire\Attributes\Computed;
use Throwable;

use function Filament\Support\is_app_url;

trait InteractsWithTwoFactorAuthentication
{
    public bool $showingQrCode = false;

    public bool $showingConfirmation = false;

    public bool $showingRecoveryCodes = false;

    public ?string $code = null;

    /**
     * @throws Exception
     */
    public function mountHasTwoFactorAuthentication(): void
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm') &&
            empty(Auth::user()->two_factor_confirmed_at)) {
            $this->disableTwoFactorAuthentication();
        }
    }

    /**
     * @throws Throwable
     */
    public function confirm(ConfirmTwoFactorAuthentication $confirm): void
    {
        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->twoFactorAuthForm->getState();

            $this->callHook('afterValidate');

            /** @var array{ code: ?string } $data */
            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $confirm(Auth::user(), $data['code'] ?? '');

            $this->callHook('afterSave');

            $this->commitDatabaseTransaction();

            $this->showingQrCode = false;
            $this->showingConfirmation = false;
            $this->showingRecoveryCodes = true;
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            $this->showingRecoveryCodes = false;

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            $this->showingRecoveryCodes = false;

            throw $exception;
        }

        Notification::make()
            ->title('Two-Factor Authentication Confirmed')
            ->body('Two-factor authentication has been confirmed.')
            ->success()
            ->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    /**
     * @return Component[]
     */
    public function getTwoFactorAuthenticationFormFields(): array
    {
        return [
            Section::make('Two-Factor Authentication')
                ->aside()
                ->description('Add additional security to your account using two factor authentication.')
                ->schema([
                    View::make('filament.two-factor-authentication.label'),

                    View::make('filament.two-factor-authentication.qr-code'),

                    TextInput::make('code')
                        ->visible(fn () => $this->showingQrCode)
                        ->label('Code')
                        ->inlineLabel(false)
                        ->placeholder('Enter the code from your authenticator'),
                ])
                ->footerActions($this->withPasswordConfirmation([
                    Action::make('enableTwoFactorAuthentication')
                        ->label('Enable')
                        ->visible(fn () => ! $this->enabled())
                        ->modalHeading('Enable Two-Factor Authentication')
                        ->failureNotificationTitle('Failed to Enable Two-Factor Authentication')
                        ->action(function (Action $action, EnableTwoFactorAuthentication $enable) {
                            try {
                                session(['auth.password_confirmed_at' => time()]);

                                $enable(Auth::user());

                                $this->showingQrCode = true;

                                if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
                                    $this->showingConfirmation = true;
                                } else {
                                    $this->showingRecoveryCodes = true;
                                }
                            } catch (Throwable $exception) {
                                report($exception);

                                $action->failure();
                                $action->cancel();
                            }
                        }),

                    Action::make('confirmCode')
                        ->visible(fn () => $this->showingConfirmation)
                        ->label('Confirm')
                        ->color('primary')
                        ->modalHeading('Confirm Two-Factor Authentication')
                        ->submit('confirm'),

                    Action::make('cancel2faConfirmation')
                        ->visible(fn () => $this->showingConfirmation)
                        ->label('Cancel')
                        ->color('gray')
                        ->modalHeading('Cancel Two-Factor Authentication Confirmation')
                        ->action(fn () => $this->disableTwoFactorAuthentication()),

                    Action::make('showRecoveryCodes')
                        ->visible(fn () => $this->enabled() && ! $this->showingRecoveryCodes && ! $this->showingConfirmation)
                        ->label('Show Recovery Codes')
                        ->color('gray')
                        ->modalHeading('Recovery Codes')
                        ->failureNotificationTitle('Failed to Show Recovery Codes')
                        ->action(fn () => $this->showingRecoveryCodes = true),

                    Action::make('regenerateRecoveryCodes')
                        ->visible(fn () => $this->showingRecoveryCodes)
                        ->label('Regenerate Recovery Codes')
                        ->color('gray')
                        ->modalHeading('Regenerate Recovery Codes')
                        ->failureNotificationTitle('Failed to Regenerate Recovery Codes')
                        ->action(function (Action $action, GenerateNewRecoveryCodes $generate) {
                            try {
                                $generate(Auth::user());
                            } catch (Throwable $exception) {
                                report($exception);

                                $action->failure();
                                $action->cancel();
                            }
                        }),

                    Action::make('disableTwoFactorAuthentication')
                        ->visible(fn () => $this->enabled() && ! $this->showingConfirmation)
                        ->label('Disable')
                        ->color('danger')
                        ->modalHeading('Disable Two-Factor Authentication')
                        ->failureNotificationTitle('Failed to Disable Two-Factor Authentication')
                        ->action(function (Action $action) {
                            try {
                                $this->disableTwoFactorAuthentication();
                            } catch (Throwable $exception) {
                                report($exception);

                                $action->failure();
                                $action->cancel();
                            }
                        }),
                ])),
        ];
    }

    public function disableTwoFactorAuthentication(): void
    {
        app(DisableTwoFactorAuthentication::class)(Auth::user());

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
    }

    #[Computed]
    public function enabled(): bool
    {
        return ! empty(Auth::user()->two_factor_secret);
    }

    /**
     * @param  Action[]  $actions
     * @return Action[]
     */
    protected function withPasswordConfirmation(array $actions): array
    {
        return Arr::map($actions, function (Action $action) {
            return $action
                ->requiresConfirmation()
                ->modal(fn () => ! $this->passwordIsConfirmed())
                ->modalDescription('For your security, please confirm your password to continue.')
                ->form([
                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->markAsRequired(false)
                        ->rule(fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if (! app(ConfirmPassword::class)(app(StatefulGuard::class), Auth::user(), $value)) {
                                $fail('The password you entered is incorrect.');
                            }
                        })
                        ->validationMessages([
                            'required' => __('Password is required.'),
                        ])
                        ->label(''),
                ]);
        });
    }
}
