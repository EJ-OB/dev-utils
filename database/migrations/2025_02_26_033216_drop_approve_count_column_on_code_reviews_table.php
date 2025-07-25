<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('code_reviews', function (Blueprint $table) {
            $table->dropColumn('approve_count');
        });
    }

    public function down(): void
    {
        Schema::table('code_reviews', function (Blueprint $table) {
            $table->integer('approve_count')->default(0);
        });
    }
};
