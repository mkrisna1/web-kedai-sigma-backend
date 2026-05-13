<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;

class ProdukSeeder extends Seeder
{
    public function run()
    {
        Produk::create([
            'kategori_id' => 1, // Kopi
            'nama_produk' => 'Caffe Latte',
            'harga_produk' => 25000,
            'deskripsi_produk' => 'Espresso dengan susu segar berkualitas.',
            'foto_produk' => '/storage/menu/latte.jpg',
            'ketersediaan_produk' => 'tersedia'
        ]);

        Produk::create([
            'kategori_id' => 3, // Makanan Ringan
            'nama_produk' => 'Kentang Goreng',
            'harga_produk' => 15000,
            'deskripsi_produk' => 'Kentang goreng renyah dengan taburan bumbu.',
            'foto_produk' => '/storage/menu/kentang.jpg',
            'ketersediaan_produk' => 'tersedia'
        ]);
    }
}
