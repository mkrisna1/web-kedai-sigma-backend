<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Reservasi;
use Carbon\Carbon;

class DashboardService
{
    public function summary(): array
    {
        $todayOrders = Pesanan::query()
            ->whereDate('tgl_pesanan', today())
            ->where('status_pesanan', '!=', 'dibatalkan');

        return [
            'pendapatan_harian' => (clone $todayOrders)->sum('total_harga'),
            'meja_terisi' => Pesanan::query()
                ->whereIn('status_pesanan', ['menunggu_konfirmasi', 'diproses'])
                ->whereNotNull('meja_id')
                ->distinct('meja_id')
                ->count('meja_id'),
            'total_meja' => Meja::count(),
            'reservasi_belum_diproses' => Reservasi::where('status_reservasi', 'menunggu_konfirmasi')->count(),
            'transaksi_terakhir' => Pesanan::with(['meja', 'detail_pesanans.produk'])
                ->latest('tgl_pesanan')
                ->limit(8)
                ->get(),
            'grafik_jam_ramai' => Pesanan::query()
                ->whereDate('tgl_pesanan', today())
                ->get()
                ->groupBy(fn ($order) => Carbon::parse($order->tgl_pesanan)->format('H:00'))
                ->map(fn ($items, $jam) => [
                    'jam' => $jam,
                    'total' => $items->count(),
                ])
                ->sortBy('jam')
                ->values(),
        ];
    }
}
