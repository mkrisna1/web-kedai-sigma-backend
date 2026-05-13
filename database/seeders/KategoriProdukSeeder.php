<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriProduk;

class KategoriProdukSeeder extends Seeder
{
    public function run()
    {
        $kategoris = ['Kopi', 'Non-Kopi', 'Makanan Ringan', 'Makanan Berat'];

        foreach ($kategoris as $kategori) {
            KategoriProduk::create([
                'nama_kategori' => $kategori
            ]);
        }
    }
}