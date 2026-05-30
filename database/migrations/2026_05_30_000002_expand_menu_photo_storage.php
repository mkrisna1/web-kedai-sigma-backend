<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('produks') || ! Schema::hasColumn('produks', 'foto_produk')) {
            return;
        }

        Schema::table('produks', function (Blueprint $table) {
            $table->longText('foto_produk')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('produks') || ! Schema::hasColumn('produks', 'foto_produk')) {
            return;
        }

        Schema::table('produks', function (Blueprint $table) {
            $table->string('foto_produk')->nullable()->change();
        });
    }
};
