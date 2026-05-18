<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        if (!Schema::hasTable('reviews')) {
            return;
        }

        DB::statement('PRAGMA foreign_keys=off');
        DB::statement('
            CREATE TABLE IF NOT EXISTS reviews_new (
                id_review INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                id_pesanan INTEGER NULL,
                nama_reviewer varchar NOT NULL,
                rating INTEGER NOT NULL,
                komentar TEXT NULL,
                balasan_admin TEXT NULL,
                waktu_dibuat datetime NOT NULL,
                created_at datetime NULL,
                updated_at datetime NULL,
                FOREIGN KEY(id_pesanan) REFERENCES pesanans(id_pesanan) ON DELETE CASCADE
            )
        ');
        DB::statement('
            INSERT INTO reviews_new (
                id_review,
                id_pesanan,
                nama_reviewer,
                rating,
                komentar,
                balasan_admin,
                waktu_dibuat,
                created_at,
                updated_at
            )
            SELECT
                id_review,
                id_pesanan,
                nama_reviewer,
                rating,
                komentar,
                balasan_admin,
                waktu_dibuat,
                created_at,
                updated_at
            FROM reviews
        ');
        DB::statement('DROP TABLE reviews');
        DB::statement('ALTER TABLE reviews_new RENAME TO reviews');
        DB::statement('PRAGMA foreign_keys=on');
    }

    public function down(): void
    {
        //
    }
};
