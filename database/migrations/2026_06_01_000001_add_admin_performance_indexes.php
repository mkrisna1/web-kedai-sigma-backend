<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->index(['status_pesanan', 'is_notif_read', 'tgl_pesanan'], 'pesanans_admin_notif_idx');
            $table->index(['status_pesanan', 'tgl_pesanan'], 'pesanans_status_date_idx');
            $table->index(['id_meja', 'status_pesanan'], 'pesanans_table_status_idx');
        });

        Schema::table('reservasis', function (Blueprint $table) {
            $table->index(['status_reservasi', 'is_notif_read', 'created_at'], 'reservasis_admin_notif_idx');
            $table->index(['id_meja', 'tgl_reservasi', 'status_reservasi'], 'reservasis_table_date_status_idx');
            $table->index(['tgl_reservasi', 'status_reservasi'], 'reservasis_date_status_idx');
        });

        Schema::table('mejas', function (Blueprint $table) {
            $table->index(['status_meja', 'used_seats'], 'mejas_status_used_idx');
        });
    }

    public function down(): void
    {
        Schema::table('mejas', function (Blueprint $table) {
            $table->dropIndex('mejas_status_used_idx');
        });

        Schema::table('reservasis', function (Blueprint $table) {
            $table->dropIndex('reservasis_date_status_idx');
            $table->dropIndex('reservasis_table_date_status_idx');
            $table->dropIndex('reservasis_admin_notif_idx');
        });

        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropIndex('pesanans_table_status_idx');
            $table->dropIndex('pesanans_status_date_idx');
            $table->dropIndex('pesanans_admin_notif_idx');
        });
    }
};
