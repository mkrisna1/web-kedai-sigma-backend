<?php

namespace App\Services;

use App\Models\Produk;

class MenuService
{
    public function getAvailable($kategoriId = null)
    {
        $query = Produk::with('kategori')
            ->orderBy('nama_produk');

        if ($kategoriId) {
            $query->where('id_kategori', $kategoriId);
        }

        return $query->get();
    }

    public function getById($id)
    {
        return Produk::with('kategori')->find($id);
    }
}
