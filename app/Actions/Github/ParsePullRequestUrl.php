<?php

declare(strict_types=1);

namespace App\Actions\Github;

use App\Data\PullRequestData;

class ParsePullRequestUrl
{
    public function __invoke(string $url): PullRequestData
    {
        $pattern = '#github\.com/([^/]+)/([^/]+)/pull/(\d+)#';

        if (preg_match($pattern, $url, $matches)) {
            return PullRequestData::from([
                'owner' => $matches[1],
                'repo' => $matches[2],
                'pullNumber' => (int) $matches[3],
            ]);
        }

        throw new \InvalidArgumentException('Invalid GitHub pull request URL.');
    }
}
