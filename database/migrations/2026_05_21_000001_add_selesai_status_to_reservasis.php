<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE reservasis MODIFY status_reservasi ENUM('menunggu_konfirmasi', 'dikonfirmasi', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu_konfirmasi'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::table('reservasis')
            ->where('status_reservasi', 'selesai')
            ->update(['status_reservasi' => 'dibatalkan']);

        DB::statement("ALTER TABLE reservasis MODIFY status_reservasi ENUM('menunggu_konfirmasi', 'dikonfirmasi', 'dibatalkan') NOT NULL DEFAULT 'menunggu_konfirmasi'");
    }
};
