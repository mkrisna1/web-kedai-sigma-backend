<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pesanans')) {
            $needsReservation = ! Schema::hasColumn('pesanans', 'id_reservasi');
            $needsPaymentStatus = ! Schema::hasColumn('pesanans', 'status_pembayaran');
            $needsOrderType = ! Schema::hasColumn('pesanans', 'tipe_pesanan');

            if ($needsReservation || $needsPaymentStatus || $needsOrderType) {
                Schema::table('pesanans', function (Blueprint $table) use (
                    $needsReservation,
                    $needsPaymentStatus,
                    $needsOrderType
                ) {
                    if ($needsReservation) {
                        $table->integer('id_reservasi')->nullable();
                    }

                    if ($needsPaymentStatus) {
                        $table->string('status_pembayaran')->default('belum_bayar');
                    }

                    if ($needsOrderType) {
                        $table->string('tipe_pesanan')->default('dine_in');
                    }
                });
            }
        }

        if (
            Schema::hasTable('detail_pesanans') &&
            ! Schema::hasColumn('detail_pesanans', 'opsi_varian')
        ) {
            Schema::table('detail_pesanans', function (Blueprint $table) {
                $table->string('opsi_varian')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detail_pesanans') && Schema::hasColumn('detail_pesanans', 'opsi_varian')) {
            Schema::table('detail_pesanans', function (Blueprint $table) {
                $table->dropColumn('opsi_varian');
            });
        }

        if (Schema::hasTable('pesanans')) {
            $columns = array_values(array_filter([
                Schema::hasColumn('pesanans', 'id_reservasi') ? 'id_reservasi' : null,
                Schema::hasColumn('pesanans', 'status_pembayaran') ? 'status_pembayaran' : null,
                Schema::hasColumn('pesanans', 'tipe_pesanan') ? 'tipe_pesanan' : null,
            ]));

            if ($columns !== []) {
                Schema::table('pesanans', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
