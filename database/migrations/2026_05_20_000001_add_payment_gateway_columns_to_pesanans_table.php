<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pesanans')) {
            return;
        }

        Schema::table('pesanans', function (Blueprint $table) {
            if (! Schema::hasColumn('pesanans', 'metode_pembayaran')) {
                $table->string('metode_pembayaran')->default('cash');
            }

            if (! Schema::hasColumn('pesanans', 'payment_provider')) {
                $table->string('payment_provider')->nullable();
            }

            if (! Schema::hasColumn('pesanans', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->index();
            }

            if (! Schema::hasColumn('pesanans', 'payment_transaction_id')) {
                $table->string('payment_transaction_id')->nullable();
            }

            if (! Schema::hasColumn('pesanans', 'payment_status')) {
                $table->string('payment_status')->nullable();
            }

            if (! Schema::hasColumn('pesanans', 'payment_qr_url')) {
                $table->text('payment_qr_url')->nullable();
            }

            if (! Schema::hasColumn('pesanans', 'payment_deeplink_url')) {
                $table->text('payment_deeplink_url')->nullable();
            }

            if (! Schema::hasColumn('pesanans', 'payment_expired_at')) {
                $table->timestamp('payment_expired_at')->nullable();
            }

            if (! Schema::hasColumn('pesanans', 'payment_status_checked_at')) {
                $table->timestamp('payment_status_checked_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pesanans')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('pesanans', 'metode_pembayaran') ? 'metode_pembayaran' : null,
            Schema::hasColumn('pesanans', 'payment_provider') ? 'payment_provider' : null,
            Schema::hasColumn('pesanans', 'payment_reference') ? 'payment_reference' : null,
            Schema::hasColumn('pesanans', 'payment_transaction_id') ? 'payment_transaction_id' : null,
            Schema::hasColumn('pesanans', 'payment_status') ? 'payment_status' : null,
            Schema::hasColumn('pesanans', 'payment_qr_url') ? 'payment_qr_url' : null,
            Schema::hasColumn('pesanans', 'payment_deeplink_url') ? 'payment_deeplink_url' : null,
            Schema::hasColumn('pesanans', 'payment_expired_at') ? 'payment_expired_at' : null,
            Schema::hasColumn('pesanans', 'payment_status_checked_at') ? 'payment_status_checked_at' : null,
        ]));

        if ($columns !== []) {
            Schema::table('pesanans', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
