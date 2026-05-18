<?php

namespace App\Services;

use App\Models\Meja;

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
        $meja = Meja::create($data);

        return $this->generateQr($meja, $frontendUrl);
    }

    public function update(Meja $meja, array $data): Meja
    {
        $data = $this->normalizePayload($data);
        $meja->update($data);

        return $meja->fresh();
    }

    public function delete(Meja $meja): void
    {
        $meja->delete();
    }

    public function generateQr(Meja $meja, ?string $frontendUrl = null): Meja
    {
        $meja->update([
            'qr_code' => $this->frontendUrl($frontendUrl) . "/qr/menu?meja_id={$meja->id}",
        ]);

        return $meja->fresh();
    }

    private function ensureDefaultTables(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $nomorMeja = 'Meja ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $legacyMeja = Meja::where('nomor_meja', 'M-' . $i)->first();
            $existingMeja = Meja::where('nomor_meja', $nomorMeja)->first();

            if ($legacyMeja && !$existingMeja) {
                $legacyMeja->update([
                    'nomor_meja' => $nomorMeja,
                    'status_meja' => 'active',
                    'used_seats' => 0,
                    'qr_code' => $this->frontendUrl() . "/qr/menu?meja_id={$legacyMeja->id}",
                ]);

                continue;
            }

            $meja = Meja::firstOrCreate(
                ['nomor_meja' => $nomorMeja],
                [
                    'status_meja' => 'active',
                    'capacity' => 4,
                    'used_seats' => 0,
                ]
            );

            if (! $meja->qr_code) {
                $meja->update([
                    'qr_code' => $this->frontendUrl() . "/qr/menu?meja_id={$meja->id}",
                ]);
            }
        }
    }

    private function normalizePayload(array $data): array
    {
        if (($data['status_meja'] ?? null) === 'maintenance') {
            $data['used_seats'] = 0;
        }

        if (isset($data['capacity'], $data['used_seats'])) {
            $data['used_seats'] = min((int) $data['used_seats'], (int) $data['capacity']);
        }

        return $data;
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
