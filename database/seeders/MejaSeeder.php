<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meja;

class MejaSeeder extends Seeder
{
    public function run()
    {
        // Bikin 5 meja dummy
        for ($i = 1; $i <= 5; $i++) {
            Meja::create([
                'nomor_meja' => 'M-' . $i,
                'qr_code' => 'qr-meja-' . $i . '.png',
                'status_meja' => 'active',
            ]);
        }
    }
}