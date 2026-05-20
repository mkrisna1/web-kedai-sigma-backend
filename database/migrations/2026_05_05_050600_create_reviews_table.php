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
    Schema::create('reviews', function (Blueprint $table) {
        $table->id('id_review');
        $table->foreignId('id_pesanan')
            ->nullable()
            ->constrained('pesanans', 'id_pesanan')
            ->onDelete('cascade');
        $table->foreignId('id_admin')
            ->nullable()
            ->constrained('admins', 'id_admin')
            ->onDelete('set null');
        $table->string('nama_reviewer');
        $table->integer('rating');
        $table->text('komentar');
        $table->text('balasan_admin')->nullable();
        $table->text('foto_review')->nullable();
        $table->unsignedInteger('likes_count')->default(0);
        $table->dateTime('waktu_dibuat')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
