<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Agent;
use Livewire\Attributes\Computed;
use stdClass;
use Throwable;

trait InteractsWithBrowserSessions
{
    /**
     * @throws AuthenticationException
     */
    public function logoutOtherBrowserSessions(StatefulGuard $guard, string $password): void
    {
        if (Config::string('session.driver') !== 'database') {
            return;
        }

        $guard->logoutOtherDevices($password);

        DB::connection(Config::string('session.connection'))
            ->table(Config::string('session.table', 'sessions'))
            ->where('user_id', Auth::user()?->getAuthIdentifier())
            ->where('id', '!=', request()->session()->getId())
            ->delete();

        request()->session()->put([
            'password_hash_'.Auth::getDefaultDriver() => Auth::user()?->getAuthPassword(),
        ]);
    }

    /**
     * @return Component[]
     */
    public function getBrowserSessionsFormFields(): array
    {
        return [
            Section::make('Browser Sessions')
                ->aside()
                ->description('Manage and log out your active sessions on other browsers and devices.')
                ->schema([
                    View::make('filament.browser-sessions.label'),

                    View::make('filament.browser-sessions.sessions'),
                ])
                ->footerActions([
                    Action::make('logoutSessions')
                        ->label('Log Out Other Browser Sessions')
                        ->requiresConfirmation()
                        ->modalHeading('Log Out Other Browser Sessions')
                        ->modalDescription('Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.')
                        ->successNotificationTitle('Your other browser sessions have been logged out.')
                        ->failureNotificationTitle('Something went wrong while logging out of your other browser sessions.')
                        ->form([
                            TextInput::make('password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->markAsRequired(false)
                                ->rule(fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                    if (! Hash::check($value, Auth::user()->password ?? '')) {
                                        $fail(__('This password does not match our records.'));
                                    }
                                })
                                ->validationMessages([
                                    'required' => __('Password is required.'),
                                ])
                                ->label(''),
                        ])
                        ->action(function (Action $action, StatefulGuard $guard, array $data) {
                            try {
                                $this->logoutOtherBrowserSessions($guard, reset($data));

                                $action->success();
                            } catch (Throwable $exception) {
                                report($exception);

                                $action->failure();
                                $action->cancel();
                            }
                        }),
                ]),
        ];
    }

    /**
     * @return Collection<int, object{ agent: Agent, ip_address: string, is_current_device: bool, last_active: string }&stdClass>
     */
    #[Computed]
    public function sessions(): Collection
    {
        if (Config::string('session.driver') !== 'database') {
            return collect();
        }

        /** @var Collection<int, object{ id: string, user_id: int, ip_address: string, user_agent: string, payload: string, last_activity: int }> $sessions */
        $sessions = DB::connection(Config::string('session.connection'))
            ->table(Config::string('session.table', 'sessions'))
            ->where('user_id', Auth::user()?->getAuthIdentifier())
            ->orderBy('last_activity', 'desc')
            ->get();

        return $sessions->map(function ($session) {
            return (object) [
                'agent' => $this->createAgent($session->user_agent),
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === request()->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        });
    }

    protected function createAgent(string $userAgent): Agent
    {
        return tap(new Agent, fn (Agent $agent) => $agent->setUserAgent($userAgent));
    }
}
