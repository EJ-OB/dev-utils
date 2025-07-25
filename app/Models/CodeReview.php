<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CodeReviewStatus;
use Database\Factories\CodeReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeReview extends Model
{
    /** @use HasFactory<CodeReviewFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'status' => CodeReviewStatus::class,
        ];
    }
}
