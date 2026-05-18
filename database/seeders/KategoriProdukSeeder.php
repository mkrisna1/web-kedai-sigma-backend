<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriProduk;

class KategoriProdukSeeder extends Seeder
{
    public function run()
    {
        $kategoris = ['Makanan', 'Kopi', 'Kopi Susu', 'Teh', 'Susu'];

        foreach ($kategoris as $kategori) {
            KategoriProduk::firstOrCreate([
                'nama_kategori' => $kategori
            ]);
        }
    }
}
