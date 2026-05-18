<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_pesanans', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('id_pesanan')
                ->constrained('pesanans', 'id_pesanan')
                ->onDelete('cascade');
            $table->foreignId('id_meja')
                ->nullable()
                ->constrained('mejas', 'id_meja')
                ->nullOnDelete();
            $table->string('nomor_meja')->nullable();
            $table->foreignId('id_produk')
                ->constrained('produks', 'id_produk');
            $table->integer('jumlah_item');
            $table->string('opsi_varian')->nullable(); // Contoh: "Ice", "Hot", "Less Sugar"
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pesanans');
    }
};
