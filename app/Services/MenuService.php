<?php

namespace App\Services;

use App\Models\Produk;

class MenuService
{
    public function getAvailable($kategoriId = null)
    {
        $query = Produk::with('kategori')
            ->where('ketersediaan_produk', 'tersedia');

        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        return $query->get();
    }

    public function getById($id)
    {
        return Produk::with('kategori')->find($id);
    }
}