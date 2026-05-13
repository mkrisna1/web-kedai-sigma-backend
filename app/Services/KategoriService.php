<?php

namespace App\Services;

use App\Models\KategoriProduk;

class KategoriService
{
    public function getAll()
    {
        return KategoriProduk::all();
    }
}