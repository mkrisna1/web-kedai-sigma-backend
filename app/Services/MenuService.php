<?php

namespace App\Services;

use App\Models\DetailPesanan;
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

    public function bestSellers(int $limit = 5)
    {
        return DetailPesanan::with(['produk.kategori'])
            ->whereHas('pesanan', function ($query) {
                $query->where('status_pesanan', 'selesai');
            })
            ->where('subtotal', '>', 0)
            ->get()
            ->filter(fn ($detail) => $detail->produk !== null)
            ->groupBy(fn ($detail) => $detail->produk->getKey())
            ->map(function ($items) {
                return [
                    'produk' => $items->first()->produk,
                    'jumlah' => $items->sum('jumlah_item'),
                    'subtotal' => $items->sum('subtotal'),
                ];
            })
            ->sort(function ($first, $second) {
                return ($second['jumlah'] <=> $first['jumlah'])
                    ?: ($second['subtotal'] <=> $first['subtotal']);
            })
            ->take(max(1, min($limit, 10)))
            ->values();
    }
}
