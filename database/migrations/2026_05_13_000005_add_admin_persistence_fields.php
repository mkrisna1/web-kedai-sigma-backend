<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mejas')) {
            Schema::table('mejas', function (Blueprint $table) {
                if (! Schema::hasColumn('mejas', 'capacity')) {
                    $table->integer('capacity')->default(4);
                }

                if (! Schema::hasColumn('mejas', 'used_seats')) {
                    $table->integer('used_seats')->default(0);
                }
            });
        }

        if (Schema::hasTable('reviews') && ! Schema::hasColumn('reviews', 'foto_review')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->text('foto_review')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'foto_review')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn('foto_review');
            });
        }

        if (Schema::hasTable('mejas')) {
            $columns = array_values(array_filter([
                Schema::hasColumn('mejas', 'capacity') ? 'capacity' : null,
                Schema::hasColumn('mejas', 'used_seats') ? 'used_seats' : null,
            ]));

            if ($columns !== []) {
                Schema::table('mejas', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
