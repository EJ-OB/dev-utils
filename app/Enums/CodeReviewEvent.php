<?php

declare(strict_types=1);

namespace App\Enums;

enum CodeReviewEvent
{
    case APPROVE;
    case REQUEST_CHANGES;
    case COMMENT;
}
