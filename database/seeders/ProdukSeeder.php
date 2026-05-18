<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProdukSeeder extends Seeder
{
    public function run()
    {
        $kategoriIds = [
            'food' => KategoriProduk::firstOrCreate(['nama_kategori' => 'Makanan'])->getKey(),
            'coffee-based' => KategoriProduk::firstOrCreate(['nama_kategori' => 'Kopi'])->getKey(),
            'coffee-milk' => KategoriProduk::firstOrCreate(['nama_kategori' => 'Kopi Susu'])->getKey(),
            'tea-series' => KategoriProduk::firstOrCreate(['nama_kategori' => 'Teh'])->getKey(),
            'milk-series' => KategoriProduk::firstOrCreate(['nama_kategori' => 'Susu'])->getKey(),
        ];

        $menus = [
            ['Matcha', 'milk-series', 13000, 'Minuman teh hijau dengan rasa khas yang creamy.', 'hot_ice'],
            ['Americano', 'coffee-based', 10000, 'Espresso yang dicampur air panas menghasilkan rasa kopi yang ringan.', 'hot_ice'],
            ['Coffee Milk', 'coffee-milk', 13000, 'Kopi susu creamy dengan manis yang pas untuk teman ngobrol.', 'none'],
            ['Coffee Bear', 'coffee-based', 16000, 'Kopi dingin bold dengan karakter rasa yang lebih tebal.', 'ice'],
            ['Espresso', 'coffee-based', 8000, 'Ekstrak kopi murni dengan rasa kuat dan aroma pekat.', 'hot'],
            ['Coffee Latte', 'coffee-milk', 13000, 'Kopi susu halus dengan karakter lembut dan nyaman diminum.', 'hot_ice'],
            ['Coffee Milk Chocolate', 'coffee-milk', 15000, 'Perpaduan kopi susu dan coklat dengan rasa lebih kaya.', 'none'],
            ['Coffee Milk V2', 'coffee-milk', 13000, 'Varian kopi susu dengan karakter manis dan creamy.', 'none'],
            ['Kopi Tubruk', 'coffee-based', 8000, 'Kopi tubruk klasik dengan rasa pekat dan aroma hangat.', 'hot'],
            ['Kopi Tubruk Susu', 'coffee-milk', 10000, 'Tubruk khas dengan tambahan susu agar terasa lebih lembut.', 'hot'],
            ['V6 Drip', 'coffee-based', 10000, 'Seduhan manual yang ringan, bersih, dan aromatik.', 'hot'],
            ['V6 Drip Susu', 'coffee-milk', 13000, 'Manual brew dengan susu untuk rasa yang lebih round.', 'hot'],
            ['Lemon Tea', 'tea-series', 10000, 'Teh segar dengan sentuhan lemon yang ringan dan wangi.', 'ice'],
            ['Lychee Tea', 'tea-series', 10000, 'Teh buah lychee yang manis, segar, dan harum.', 'ice'],
            ['Milo', 'milk-series', 13000, 'Minuman coklat malt yang creamy dan familiar.', 'ice'],
            ['Joshua', 'milk-series', 13000, 'Minuman manis menyegarkan dengan karakter lembut.', 'ice'],
            ['Strawberry Milk', 'milk-series', 15000, 'Susu stroberi dengan rasa manis buah yang ringan.', 'ice'],
            ['Teh Tarik', 'tea-series', 13000, 'Teh susu berbusa dengan rasa legit dan harum.', 'none'],
            ['Redvelvet', 'milk-series', 13000, 'Minuman creamy dengan karakter manis khas red velvet.', 'hot_ice'],
            ['Coklat Classic', 'milk-series', 13000, 'Coklat klasik yang pekat, lembut, dan comforting.', 'hot_ice'],
            ['Coklat Classic Roti', 'milk-series', 15000, 'Coklat creamy dengan sentuhan rasa roti yang khas.', 'hot_ice'],
            ['Ayam Popcorn', 'food', 15000, 'Potongan ayam kecil yang digoreng crispy dengan rasa gurih dan renyah.', 'none'],
            ['Kentang', 'food', 10000, 'Kentang goreng renyah dengan rasa gurih, cocok buat camilan santai.', 'none'],
            ['Risol Mayo', 'food', 13000, 'Risol gurih berisi mayo lembut, enak buat pembuka pesanan.', 'none'],
            ['Sosis Solo', 'food', 13000, 'Camilan gurih berisi daging lembut dengan balutan tipis.', 'none'],
            ['Tahu Bakso Goreng', 'food', 13000, 'Tahu bakso goreng renyah dengan isi yang gurih.', 'none'],
            ['Piscok', 'food', 13000, 'Pisang coklat crispy dengan rasa manis yang pas.', 'none'],
            ['Nugget', 'food', 13000, 'Nugget goreng hangat untuk camilan praktis.', 'none'],
            ['Siomay Ayam', 'food', 15000, 'Siomay ayam lembut dengan cita rasa gurih.', 'none'],
            ['Mix Platter', 'food', 20000, 'Pilihan snack campur untuk dinikmati bareng.', 'none'],
            ['Indomie Nyemek Halu', 'food', 15000, 'Indomie nyemek dengan rasa gurih yang lebih berani.', 'none'],
            ['Indomie Nyemek Vinsen', 'food', 15000, 'Indomie nyemek hangat dengan racikan khas Kedai Sigma.', 'none'],
        ];

        $hasTemperatureOption = Schema::hasColumn('produks', 'opsi_suhu');

        foreach ($menus as [$name, $category, $price, $description, $temperatureOption]) {
            $payload = [
                'id_kategori' => $kategoriIds[$category],
                'harga_produk' => $price,
                'deskripsi_produk' => $description,
                'ketersediaan_produk' => 'tersedia',
            ];

            if ($hasTemperatureOption) {
                $payload['opsi_suhu'] = $temperatureOption;
            }

            Produk::updateOrCreate(
                ['nama_produk' => $name],
                $payload
            );
        }
    }
}
