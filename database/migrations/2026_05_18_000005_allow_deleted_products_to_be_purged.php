<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('detail_pesanans') &&
            Schema::hasColumn('detail_pesanans', 'id_produk')
        ) {
            Schema::table('detail_pesanans', function (Blueprint $table) {
                $table->dropForeign(['id_produk']);
            });

            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE detail_pesanans MODIFY id_produk BIGINT UNSIGNED NULL');
            } else {
                Schema::table('detail_pesanans', function (Blueprint $table) {
                    $table->unsignedBigInteger('id_produk')->nullable()->change();
                });
            }

            $deletedProductIds = DB::table('produks')
                ->whereNotNull('deleted_at')
                ->pluck('id_produk');

            if ($deletedProductIds->isNotEmpty()) {
                DB::table('detail_pesanans')
                    ->whereIn('id_produk', $deletedProductIds)
                    ->update(['id_produk' => null]);

                DB::table('produks')
                    ->whereIn('id_produk', $deletedProductIds)
                    ->delete();
            }

            Schema::table('detail_pesanans', function (Blueprint $table) {
                $table
                    ->foreign('id_produk', 'detail_pesanans_id_produk_foreign')
                    ->references('id_produk')
                    ->on('produks')
                    ->nullOnDelete();
            });
        }

        if (
            Schema::hasTable('kategori_produks') &&
            Schema::hasTable('produks')
        ) {
            $kopiId = DB::table('kategori_produks')
                ->where('nama_kategori', 'Kopi')
                ->value('id_kategori');
            $kopiSusuIds = DB::table('kategori_produks')
                ->where('nama_kategori', 'Kopi Susu')
                ->pluck('id_kategori');

            if ($kopiId && $kopiSusuIds->isNotEmpty()) {
                DB::table('produks')
                    ->whereIn('id_kategori', $kopiSusuIds)
                    ->update(['id_kategori' => $kopiId]);
            }

            DB::table('kategori_produks')
                ->where('nama_kategori', 'Kopi Susu')
                ->delete();
        }
    }

    public function down(): void
    {
        //
    }
};
