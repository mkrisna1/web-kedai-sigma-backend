<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mejas')) {
            return;
        }

        $tables = DB::table('mejas')
            ->orderBy('id_meja')
            ->get();

        if ($tables->isEmpty()) {
            return;
        }

        $usedNumbers = [];
        $mappings = [];
        $tempBase = ((int) $tables->max('id_meja')) + 1000;

        foreach ($tables as $index => $table) {
            $number = $this->extractTableNumber($table->nomor_meja) ?? (int) $table->id_meja;

            while (isset($usedNumbers[$number])) {
                $number++;
            }

            $usedNumbers[$number] = true;
            $mappings[] = [
                'old_id' => (int) $table->id_meja,
                'temp_id' => $tempBase + $index + 1,
                'new_id' => $number,
                'nomor_meja' => $this->formatTableName($number),
                'qr_code' => $this->replaceQrTableId($table->qr_code, $number),
            ];
        }

        DB::transaction(function () use ($mappings) {
            Schema::disableForeignKeyConstraints();

            try {
                foreach ($mappings as $mapping) {
                    $this->updateTableReferences($mapping['old_id'], $mapping['temp_id']);

                    DB::table('mejas')
                        ->where('id_meja', $mapping['old_id'])
                        ->update(['id_meja' => $mapping['temp_id']]);
                }

                foreach ($mappings as $mapping) {
                    DB::table('mejas')
                        ->where('id_meja', $mapping['temp_id'])
                        ->update([
                            'id_meja' => $mapping['new_id'],
                            'nomor_meja' => $mapping['nomor_meja'],
                            'qr_code' => $mapping['qr_code'],
                            'updated_at' => now(),
                        ]);

                    $this->updateTableReferences($mapping['temp_id'], $mapping['new_id']);
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });

        Schema::table('mejas', function (Blueprint $table) {
            $table->unique('nomor_meja', 'mejas_nomor_meja_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mejas')) {
            return;
        }

        Schema::table('mejas', function (Blueprint $table) {
            $table->dropUnique('mejas_nomor_meja_unique');
        });
    }

    private function updateTableReferences(int $fromId, int $toId): void
    {
        foreach (['reservasis', 'pesanans', 'detail_pesanans'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id_meja')) {
                continue;
            }

            DB::table($table)
                ->where('id_meja', $fromId)
                ->update(['id_meja' => $toId]);
        }
    }

    private function extractTableNumber(?string $value): ?int
    {
        if (! preg_match('/\d+/', (string) $value, $matches)) {
            return null;
        }

        return max((int) $matches[0], 1);
    }

    private function formatTableName(int $number): string
    {
        return 'Meja ' . str_pad((string) $number, 2, '0', STR_PAD_LEFT);
    }

    private function replaceQrTableId(?string $qrCode, int $tableId): ?string
    {
        if (! $qrCode) {
            return null;
        }

        if (preg_match('/([?&]meja_id=)\d+/', $qrCode)) {
            return preg_replace('/([?&]meja_id=)\d+/', '${1}' . $tableId, $qrCode);
        }

        return $qrCode;
    }
};
