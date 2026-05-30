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

        DB::statement('PRAGMA foreign_keys=off');

        if (Schema::hasTable('reservasis')) {
            DB::statement(<<<'SQL'
                CREATE TABLE IF NOT EXISTS reservasis_new (
                    id_reservasi integer primary key autoincrement not null,
                    id_meja integer not null,
                    nama_reservasi varchar not null,
                    no_hp varchar not null,
                    tgl_reservasi date not null,
                    jam_reservasi time not null,
                    jml_orang integer not null,
                    status_reservasi varchar check (status_reservasi in ('menunggu_konfirmasi', 'dikonfirmasi', 'selesai', 'dibatalkan')) not null default 'menunggu_konfirmasi',
                    catatan_reservasi varchar,
                    created_at datetime,
                    updated_at datetime,
                    foreign key(id_meja) references mejas(id_meja) on delete cascade
                )
            SQL);

            DB::statement(<<<'SQL'
                INSERT INTO reservasis_new (
                    id_reservasi,
                    id_meja,
                    nama_reservasi,
                    no_hp,
                    tgl_reservasi,
                    jam_reservasi,
                    jml_orang,
                    status_reservasi,
                    catatan_reservasi,
                    created_at,
                    updated_at
                )
                SELECT
                    id_reservasi,
                    id_meja,
                    nama_reservasi,
                    no_hp,
                    tgl_reservasi,
                    jam_reservasi,
                    jml_orang,
                    CASE status_reservasi
                        WHEN 'menunggu konfirmasi' THEN 'menunggu_konfirmasi'
                        ELSE status_reservasi
                    END,
                    catatan_reservasi,
                    created_at,
                    updated_at
                FROM reservasis
            SQL);

            DB::statement('DROP TABLE reservasis');
            DB::statement('ALTER TABLE reservasis_new RENAME TO reservasis');
        }

        if (Schema::hasTable('pesanans')) {
            DB::statement(<<<'SQL'
                CREATE TABLE IF NOT EXISTS pesanans_new (
                    id_pesanan integer primary key autoincrement not null,
                    id_meja integer not null,
                    tgl_pesanan datetime not null,
                    status_pesanan varchar check (status_pesanan in ('menunggu_konfirmasi', 'diproses', 'selesai', 'dibatalkan')) not null default 'menunggu_konfirmasi',
                    total_harga numeric not null,
                    catatan_pesanan varchar,
                    created_at datetime,
                    updated_at datetime,
                    foreign key(id_meja) references mejas(id_meja) on delete cascade
                )
            SQL);

            DB::statement(<<<'SQL'
                INSERT INTO pesanans_new (
                    id_pesanan,
                    id_meja,
                    tgl_pesanan,
                    status_pesanan,
                    total_harga,
                    catatan_pesanan,
                    created_at,
                    updated_at
                )
                SELECT
                    id_pesanan,
                    id_meja,
                    tgl_pesanan,
                    status_pesanan,
                    total_harga,
                    catatan_pesanan,
                    created_at,
                    updated_at
                FROM pesanans
            SQL);

            DB::statement('DROP TABLE pesanans');
            DB::statement('ALTER TABLE pesanans_new RENAME TO pesanans');
        }

        DB::statement('PRAGMA foreign_keys=on');
    }

    public function down(): void
    {
        //
    }
};
