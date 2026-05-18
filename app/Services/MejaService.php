<?php

namespace App\Services;

use App\Models\Meja;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MejaService
{
    public function __construct(
        protected TableAvailabilityService $tableAvailabilityService
    ) {
    }

    public function getAll()
    {
        $this->ensureDefaultTables();
        $this->tableAvailabilityService->releaseStaleOccupiedTables();

        return Meja::orderBy('nomor_meja')->get();
    }

    public function create(array $data, ?string $frontendUrl = null): Meja
    {
        $data = $this->normalizePayload($data);
        $tableNumber = $this->extractTableNumber($data['nomor_meja'] ?? null);

        if (! $tableNumber) {
            throw ValidationException::withMessages([
                'nomor_meja' => 'Nomor meja wajib memakai angka.',
            ]);
        }

        if (Meja::whereKey($tableNumber)->exists()) {
            throw ValidationException::withMessages([
                'nomor_meja' => 'Nomor meja sudah digunakan.',
            ]);
        }

        $meja = new Meja($data);
        $meja->setAttribute($meja->getKeyName(), $tableNumber);
        $meja->save();

        return $this->generateQr($meja, $frontendUrl);
    }

    public function update(Meja $meja, array $data): Meja
    {
        $data = $this->normalizePayload($data);

        if (array_key_exists('nomor_meja', $data)) {
            $tableNumber = $this->extractTableNumber($data['nomor_meja']);

            if (! $tableNumber) {
                throw ValidationException::withMessages([
                    'nomor_meja' => 'Nomor meja wajib memakai angka.',
                ]);
            }

            if ($tableNumber !== (int) $meja->getKey()) {
                if (Meja::whereKey($tableNumber)->exists()) {
                    throw ValidationException::withMessages([
                        'nomor_meja' => 'Nomor meja sudah digunakan.',
                    ]);
                }

                $meja = $this->moveTablePrimaryKey($meja, $tableNumber);
            }
        }

        $meja->update($data);

        return array_key_exists('nomor_meja', $data)
            ? $this->generateQr($meja->fresh())
            : $meja->fresh();
    }

    public function delete(Meja $meja): void
    {
        $meja->delete();
    }

    public function generateQr(Meja $meja, ?string $frontendUrl = null): Meja
    {
        $meja->update([
            'qr_code' => $this->frontendUrl($frontendUrl) . "/qr/menu?meja_id={$meja->getKey()}",
        ]);

        return $meja->fresh();
    }

    private function ensureDefaultTables(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $nomorMeja = 'Meja ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $legacyMeja = Meja::where('nomor_meja', 'M-' . $i)->first();
            $existingMeja = Meja::whereKey($i)->first() ?: Meja::where('nomor_meja', $nomorMeja)->first();

            if ($legacyMeja && !$existingMeja) {
                $legacyMeja->update([
                    'nomor_meja' => $nomorMeja,
                    'status_meja' => 'active',
                    'used_seats' => 0,
                    'qr_code' => $this->frontendUrl() . "/qr/menu?meja_id={$legacyMeja->getKey()}",
                ]);

                continue;
            }

            $meja = $existingMeja;

            if (! $meja) {
                $meja = new Meja([
                    'nomor_meja' => $nomorMeja,
                    'status_meja' => 'active',
                    'capacity' => 4,
                    'used_seats' => 0,
                ]);
                $meja->setAttribute($meja->getKeyName(), $i);
                $meja->save();
            } elseif ($meja->nomor_meja !== $nomorMeja) {
                $meja->update(['nomor_meja' => $nomorMeja]);
            }

            if (! $meja->qr_code) {
                $meja->update([
                    'qr_code' => $this->frontendUrl() . "/qr/menu?meja_id={$meja->getKey()}",
                ]);
            }
        }
    }

    private function normalizePayload(array $data): array
    {
        if (array_key_exists('nomor_meja', $data)) {
            $tableNumber = $this->extractTableNumber($data['nomor_meja']);

            if ($tableNumber) {
                $data['nomor_meja'] = $this->formatTableName($tableNumber);
            }
        }

        if (($data['status_meja'] ?? null) === 'maintenance') {
            $data['used_seats'] = 0;
        }

        if (isset($data['capacity'], $data['used_seats'])) {
            $data['used_seats'] = min((int) $data['used_seats'], (int) $data['capacity']);
        }

        return $data;
    }

    private function moveTablePrimaryKey(Meja $meja, int $newId): Meja
    {
        $oldId = (int) $meja->getKey();

        DB::transaction(function () use ($oldId, $newId) {
            Schema::disableForeignKeyConstraints();

            try {
                DB::table('mejas')
                    ->where('id_meja', $oldId)
                    ->update(['id_meja' => $newId]);

                foreach (['reservasis', 'pesanans', 'detail_pesanans'] as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'id_meja')) {
                        DB::table($table)
                            ->where('id_meja', $oldId)
                            ->update(['id_meja' => $newId]);
                    }
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });

        return Meja::findOrFail($newId);
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

    private function frontendUrl(?string $frontendUrl = null): string
    {
        $url = trim($frontendUrl ?: env('FRONTEND_URL', 'http://127.0.0.1:5173'));

        if (! preg_match('/^https?:\/\//i', $url)) {
            $url = 'http://127.0.0.1:5173';
        }

        return rtrim($url, '/');
    }
}
