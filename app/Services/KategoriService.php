<?php

namespace App\Services;

use App\Models\KategoriProduk;

class KategoriService
{
    private const CANONICAL_CATEGORIES = [
        'Makanan',
        'Kopi',
        'Kopi Susu',
        'Teh',
        'Susu',
    ];

    public function getAll()
    {
        foreach (self::CANONICAL_CATEGORIES as $category) {
            KategoriProduk::firstOrCreate(['nama_kategori' => $category]);
        }

        return KategoriProduk::whereIn('nama_kategori', self::CANONICAL_CATEGORIES)
            ->get()
            ->sortBy(fn ($category) => array_search($category->nama_kategori, self::CANONICAL_CATEGORIES, true))
            ->values();
    }
}
