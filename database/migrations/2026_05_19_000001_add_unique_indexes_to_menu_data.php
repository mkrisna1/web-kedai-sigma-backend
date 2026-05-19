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
            Schema::hasTable('kategori_produks') &&
            Schema::hasColumn('kategori_produks', 'nama_kategori') &&
            ! $this->hasIndex('kategori_produks', 'kategori_produks_nama_kategori_unique')
        ) {
            DB::table('kategori_produks')->update([
                'nama_kategori' => DB::raw('TRIM(nama_kategori)'),
            ]);

            Schema::table('kategori_produks', function (Blueprint $table) {
                $table->unique('nama_kategori', 'kategori_produks_nama_kategori_unique');
            });
        }

        if (
            Schema::hasTable('produks') &&
            Schema::hasColumn('produks', 'nama_produk') &&
            ! $this->hasIndex('produks', 'produks_nama_produk_unique')
        ) {
            DB::table('produks')->update([
                'nama_produk' => DB::raw('TRIM(nama_produk)'),
            ]);

            Schema::table('produks', function (Blueprint $table) {
                $table->unique('nama_produk', 'produks_nama_produk_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex('produks', 'produks_nama_produk_unique')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->dropUnique('produks_nama_produk_unique');
            });
        }

        if ($this->hasIndex('kategori_produks', 'kategori_produks_nama_kategori_unique')) {
            Schema::table('kategori_produks', function (Blueprint $table) {
                $table->dropUnique('kategori_produks_nama_kategori_unique');
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = database()')
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
