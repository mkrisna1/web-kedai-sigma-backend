<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kategori_produks') || ! Schema::hasTable('produks')) {
            return;
        }

        $coffeeCategoryId = DB::table('kategori_produks')
            ->where('nama_kategori', 'Kopi')
            ->value('id_kategori');

        if (! $coffeeCategoryId) {
            $coffeeCategoryId = DB::table('kategori_produks')->insertGetId([
                'nama_kategori' => 'Kopi',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $coffeeMilkCategoryId = DB::table('kategori_produks')
            ->where('nama_kategori', 'Kopi Susu')
            ->value('id_kategori');

        if (! $coffeeMilkCategoryId) {
            return;
        }

        DB::table('produks')
            ->where('id_kategori', $coffeeMilkCategoryId)
            ->update([
                'id_kategori' => $coffeeCategoryId,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('kategori_produks')) {
            return;
        }

        DB::table('kategori_produks')->updateOrInsert(
            ['nama_kategori' => 'Kopi Susu'],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }
};
