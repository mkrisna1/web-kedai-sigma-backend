<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews') || ! Schema::hasColumn('reviews', 'foto_review')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->longText('foto_review')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reviews') || ! Schema::hasColumn('reviews', 'foto_review')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->text('foto_review')->nullable()->change();
        });
    }
};
