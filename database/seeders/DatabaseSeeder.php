<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            AdminSeeder::class,
            MejaSeeder::class,
            KategoriProdukSeeder::class,
            ProdukSeeder::class,
            TransaksiSeeder::class, 
        ]);
    }
}
