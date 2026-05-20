<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('produks') || Schema::hasColumn('produks', 'opsi_suhu')) {
            return;
        }

        Schema::table('produks', function (Blueprint $table) {
            $table->string('opsi_suhu')->default('none')->after('foto_produk');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('produks') || ! Schema::hasColumn('produks', 'opsi_suhu')) {
            return;
        }

        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('opsi_suhu');
        });
    }
};
