<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        Review::create([
            'nama_reviewer' => 'Budi Sudarsono',
            'rating' => 5,
            'komentar' => 'Kopi nya mantap beneran bikin fokus ngerjain tugas! + makanannya enak!, tempatnya nya juga nyantai',
            'id_admin' => 1,
            'balasan_admin' => 'Terima kasih kak Budi, ditunggu kedatangan berikutnya!',
            'waktu_dibuat' => now(),
        ]);
    }
}
