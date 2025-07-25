<?php

declare(strict_types=1);

use App\Enums\CodeReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('project');
            $table->string('link');
            $table->string('status')->default(CodeReviewStatus::PENDING->value);
            $table->string('description')->nullable();
            $table->integer('approve_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_reviews');
    }
};
