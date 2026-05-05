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
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meja_id')->nullable()->constrained('mejas');
            $table->dateTime('tgl_pesanan');
            $table->enum('status_pesanan', ['menunggu_konfirmasi', 'diproses', 'selesai', 'dibatalkan'])->default('menunggu_konfirmasi');
            $table->enum('status_pembayaran', ['belum_bayar', 'lunas'])->default('belum_bayar');
            $table->enum('tipe_pesanan', ['dine_in', 'takeaway']);
            $table->decimal('total_harga', 15, 2);
            $table->text('catatan_pesanan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
