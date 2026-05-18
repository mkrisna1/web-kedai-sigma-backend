<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews') && ! Schema::hasColumn('reviews', 'likes_count')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unsignedInteger('likes_count')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'likes_count')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn('likes_count');
            });
        }
    }
};
