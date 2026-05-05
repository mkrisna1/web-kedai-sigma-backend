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
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori_produks')->onDelete('cascade');
            $table->string('nama_produk');
             $table->decimal('harga_produk', 15, 2);
            $table->text('deskripsi_produk')->nullable();
            $table->string('foto_produk')->nullable();
            $table->enum('ketersediaan_produk', ['tersedia', 'tidak_tersedia'])->default('tersedia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
