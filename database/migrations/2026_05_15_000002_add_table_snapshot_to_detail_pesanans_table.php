<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('detail_pesanans')) {
            return;
        }

        Schema::table('detail_pesanans', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_pesanans', 'id_meja')) {
                $table->foreignId('id_meja')
                    ->nullable()
                    ->after('id_pesanan')
                    ->constrained('mejas', 'id_meja')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('detail_pesanans', 'nomor_meja')) {
                $table->string('nomor_meja')->nullable()->after('id_meja');
            }
        });

        if (
            Schema::hasColumn('detail_pesanans', 'id_meja') &&
            Schema::hasColumn('detail_pesanans', 'nomor_meja')
        ) {
            DB::table('detail_pesanans')
                ->join('pesanans', 'detail_pesanans.id_pesanan', '=', 'pesanans.id_pesanan')
                ->leftJoin('mejas', 'pesanans.id_meja', '=', 'mejas.id_meja')
                ->whereNull('detail_pesanans.id_meja')
                ->update([
                    'detail_pesanans.id_meja' => DB::raw('pesanans.id_meja'),
                    'detail_pesanans.nomor_meja' => DB::raw('mejas.nomor_meja'),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('detail_pesanans')) {
            return;
        }

        Schema::table('detail_pesanans', function (Blueprint $table) {
            if (Schema::hasColumn('detail_pesanans', 'id_meja')) {
                $table->dropConstrainedForeignId('id_meja');
            }

            if (Schema::hasColumn('detail_pesanans', 'nomor_meja')) {
                $table->dropColumn('nomor_meja');
            }
        });
    }
};
