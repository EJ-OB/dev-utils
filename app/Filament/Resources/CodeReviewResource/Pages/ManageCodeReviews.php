<?php

declare(strict_types=1);

namespace App\Filament\Resources\CodeReviewResource\Pages;

use App\Actions\Github\ParsePullRequestUrl;
use App\Enums\CodeReviewEvent;
use App\Enums\CodeReviewStatus;
use App\Filament\Resources\CodeReviewResource;
use App\Filament\Resources\CodeReviewResource\Widgets\WeeklyCodeReviews;
use App\Models\CodeReview;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class ManageCodeReviews extends ManageRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CodeReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('approve')
                ->modalWidth(MaxWidth::Medium)
                ->label('Approve PR')
                ->color('success')
                ->form([
                    TextInput::make('pull_request_url')
                        ->label('Pull Request URL')
                        ->required()
                        ->url(),

                    Textarea::make('comment')
                        ->maxLength(2048),
                ])
                ->action(function (ParsePullRequestUrl $parse, array $data) {
                    $token = Config::string('services.github.token');
                    $pr = $parse($data['pull_request_url'] ?? '');
                    $comment = $data['comment'] ?? '';

                    $response = Http::baseUrl(Config::string('services.github.base_url'))
                        ->withHeaders([
                            'Authorization' => "Bearer $token",
                            'X-GitHub-Api-Version' => Config::string('services.github.version'),
                        ])
                        ->post($pr->toCodeReviewPath(), [
                            ...$comment ? ['body' => $data['comment']] : [],
                            'event' => CodeReviewEvent::APPROVE->name,
                        ]);

                    if ($response->ok()) {
                        CodeReview::create([
                            'project' => $pr->getProject(),
                            'link' => $data['pull_request_url'],
                            'status' => CodeReviewStatus::APPROVED,
                        ]);

                        Notification::make('approved')
                            ->success()
                            ->title('Pull Request Approved')
                            ->body('The pull request has been approved successfully.')
                            ->send();
                    } else {
                        Notification::make('approval_failed')
                            ->danger()
                            ->title('Approval Failed')
                            ->body('There was an error approving the pull request.')
                            ->send();
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [WeeklyCodeReviews::class];
    }
}
