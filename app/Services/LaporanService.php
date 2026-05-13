<?php

namespace App\Services;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use Carbon\Carbon;

class LaporanService
{
    public function summary(string $period = 'day', ?string $date = null): array
    {
        [$start, $end] = $this->resolveRange($period, $date);

        $orders = Pesanan::with(['meja', 'detail_pesanans.produk.kategori'])
            ->whereBetween('tgl_pesanan', [$start, $end])
            ->latest('tgl_pesanan')
            ->get();

        $validOrders = $orders->where('status_pesanan', '!=', 'dibatalkan');

        $details = DetailPesanan::with('produk.kategori')
            ->whereHas('pesanan', function ($query) use ($start, $end) {
                $query->whereBetween('tgl_pesanan', [$start, $end])
                    ->where('status_pesanan', '!=', 'dibatalkan');
            })
            ->get();

        return [
            'period' => $period,
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
            'total_penjualan' => $validOrders->sum('total_harga'),
            'total_order' => $validOrders->count(),
            'best_seller_menu' => $details
                ->groupBy('produk_id')
                ->map(function ($items) {
                    return [
                        'produk' => $items->first()->produk,
                        'jumlah' => $items->sum('jumlah_item'),
                        'subtotal' => $items->sum('subtotal'),
                    ];
                })
                ->sortByDesc('jumlah')
                ->take(5)
                ->values(),
            'kategori_populer' => $details
                ->groupBy(fn ($item) => $item->produk?->kategori?->nama_kategori ?? 'Tanpa Kategori')
                ->map(fn ($items, $kategori) => [
                    'kategori' => $kategori,
                    'jumlah' => $items->sum('jumlah_item'),
                ])
                ->sortByDesc('jumlah')
                ->values(),
            'jam_sibuk' => $validOrders
                ->groupBy(fn ($order) => Carbon::parse($order->tgl_pesanan)->format('H:00'))
                ->map(fn ($items, $jam) => [
                    'jam' => $jam,
                    'total' => $items->count(),
                ])
                ->sortByDesc('total')
                ->values(),
            'transaksi' => $orders->values(),
        ];
    }

    private function resolveRange(string $period, ?string $date): array
    {
        $base = $date ? Carbon::parse($date) : now();

        return match ($period) {
            'week' => [$base->copy()->startOfWeek(), $base->copy()->endOfWeek()],
            'month' => [$base->copy()->startOfMonth(), $base->copy()->endOfMonth()],
            default => [$base->copy()->startOfDay(), $base->copy()->endOfDay()],
        };
    }
}
