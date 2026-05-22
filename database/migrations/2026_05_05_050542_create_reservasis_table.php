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
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id('id_reservasi');
            $table->foreignId('id_meja')
                ->nullable()
                ->constrained('mejas', 'id_meja')
                ->onDelete('set null');
            $table->foreignId('id_admin')
                ->nullable()
                ->constrained('admins', 'id_admin')
                ->onDelete('set null');
            $table->string('nama_reservasi');
            $table->string('no_hp');
            $table->date('tgl_reservasi');
            $table->time('jam_reservasi');
            $table->integer('jml_orang');
            $table->enum('status_reservasi', ['menunggu_konfirmasi', 'dikonfirmasi', 'selesai', 'dibatalkan'])->default('menunggu_konfirmasi');
            $table->string('catatan_reservasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservasis');
    }
};
