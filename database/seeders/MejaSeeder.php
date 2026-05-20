<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meja;

class MejaSeeder extends Seeder
{
    public function run()
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://127.0.0.1:5173'), '/');

        for ($i = 1; $i <= 8; $i++) {
            $nomorMeja = 'Meja ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $legacyMeja = Meja::where('nomor_meja', 'M-' . $i)->first();
            $existingMeja = Meja::where('nomor_meja', $nomorMeja)->first();

            if ($legacyMeja && !$existingMeja) {
                $legacyMeja->update([
                    'nomor_meja' => $nomorMeja,
                    'qr_code' => "{$frontendUrl}/qr/menu?meja_id={$legacyMeja->id}",
                    'status_meja' => 'active',
                    'used_seats' => 0,
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

            $meja->update([
                'qr_code' => "{$frontendUrl}/qr/menu?meja_id={$meja->id}",
                'status_meja' => 'active',
                'used_seats' => 0,
            ]);
        }
    }
}
