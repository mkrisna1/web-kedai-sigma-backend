<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('produks')) {
            return;
        }

        Schema::table('produks', function (Blueprint $table) {
            if (! Schema::hasColumn('produks', 'harga_hot')) {
                $table->decimal('harga_hot', 15, 2)->nullable()->after('harga_produk');
            }

            if (! Schema::hasColumn('produks', 'harga_ice')) {
                $table->decimal('harga_ice', 15, 2)->nullable()->after('harga_hot');
            }
        });

        $variantPrices = [
            'Matcha' => ['harga_hot' => 15000, 'harga_ice' => 13000],
            'Americano' => ['harga_hot' => 10000, 'harga_ice' => 13000],
            'Coffee Latte' => ['harga_hot' => 15000, 'harga_ice' => 13000],
            'Redvelvet' => ['harga_hot' => 15000, 'harga_ice' => 13000],
            'Coklat Classic' => ['harga_hot' => 13000, 'harga_ice' => 13000],
            'Coklat Classic Roti' => ['harga_hot' => 15000, 'harga_ice' => 15000],
        ];

        foreach ($variantPrices as $name => $prices) {
            DB::table('produks')
                ->where('nama_produk', $name)
                ->update($prices);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('produks')) {
            return;
        }

        Schema::table('produks', function (Blueprint $table) {
            if (Schema::hasColumn('produks', 'harga_ice')) {
                $table->dropColumn('harga_ice');
            }

            if (Schema::hasColumn('produks', 'harga_hot')) {
                $table->dropColumn('harga_hot');
            }
        });
    }
};
