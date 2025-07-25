<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class PullRequestData extends Data
{
    public function __construct(
        public string $owner,
        public string $repo,
        public int $pullNumber,
    ) {}

    public function toCodeReviewPath(): string
    {
        return "repos/{$this->owner}/{$this->repo}/pulls/{$this->pullNumber}/reviews";
    }

    public function getProject(): string
    {
        return str($this->repo)->headline()->toString();
    }
}
