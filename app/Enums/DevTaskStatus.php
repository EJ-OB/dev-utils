<?php

declare(strict_types=1);

namespace App\Enums;

enum DevTaskStatus: string
{
    case PENDING = 'pending';
    case ONGOING = 'ongoing';
    case ON_HOLD = 'on_hold';
    case FOR_QA = 'for_qa';
    case FOR_UAT = 'for_uat';
    case FOR_RELEASE = 'for_release';
    case COMPLETED = 'completed';

    public function diffForHumans(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ONGOING => 'Ongoing',
            self::ON_HOLD => 'On Hold',
            self::FOR_QA => 'For QA',
            self::FOR_UAT => 'For UAT',
            self::FOR_RELEASE => 'For Release',
            self::COMPLETED => 'Completed',
        };
    }
}
