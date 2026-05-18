<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('produks') || ! Schema::hasColumn('produks', 'deleted_at')) {
            return;
        }

        $query = DB::table('produks')->whereNotNull('deleted_at');

        if (Schema::hasTable('detail_pesanans') && Schema::hasColumn('detail_pesanans', 'id_produk')) {
            $query->whereNotExists(function ($subQuery) {
                $subQuery
                    ->selectRaw('1')
                    ->from('detail_pesanans')
                    ->whereColumn('detail_pesanans.id_produk', 'produks.id_produk');
            });
        }

        $query->delete();
    }

    public function down(): void
    {
        //
    }
};
