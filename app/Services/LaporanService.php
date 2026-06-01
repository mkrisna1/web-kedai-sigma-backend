<?php

namespace App\Services;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use Carbon\Carbon;

class LaporanService
{
    public function summary(
        string $period = 'day',
        ?string $date = null,
        string $exportPeriod = 'day'
    ): array
    {
        $isAllTime = $date === 'all';
        $base = ($date && !$isAllTime) ? Carbon::parse($date) : now();
        [$summaryStart, $summaryEnd] = $this->resolveRange($period, $isAllTime ? null : $date);
        $logStart = $summaryStart;
        $logEnd = $summaryEnd;
        [$exportStart, $exportEnd] = $this->resolveExportRange($exportPeriod, $base);
        [$previousSummaryStart, $previousSummaryEnd] = $this->resolvePreviousRange($period, $summaryStart, $summaryEnd);

        $orders = Pesanan::with(['meja', 'detail_pesanans.produk.kategori'])
            ->when(!$isAllTime, fn ($query) => $query->whereBetween('tgl_pesanan', [$summaryStart, $summaryEnd]))
            ->latest('tgl_pesanan')
            ->get();

        $validOrders = $orders->whereIn('status_pesanan', ['diproses', 'selesai']);
        $previousValidOrders = Pesanan::query()
            ->when(!$isAllTime, fn ($query) => $query->whereBetween('tgl_pesanan', [$previousSummaryStart, $previousSummaryEnd]))
            ->whereIn('status_pesanan', ['diproses', 'selesai'])
            ->get();
        $logOrdersQuery = Pesanan::with(['meja', 'detail_pesanans.produk.kategori'])
            ->whereIn('status_pesanan', ['diproses', 'selesai'])
            ->latest('tgl_pesanan');
            
        if (!$isAllTime) {
            $logOrdersQuery->whereBetween('tgl_pesanan', [$logStart, $logEnd]);
        }
        $logOrders = $logOrdersQuery->get();

        $exportOrdersQuery = Pesanan::with(['meja', 'detail_pesanans.produk.kategori'])
            ->whereIn('status_pesanan', ['diproses', 'selesai'])
            ->latest('tgl_pesanan');
            
        if (!$isAllTime) {
            $exportOrdersQuery->whereBetween('tgl_pesanan', [$exportStart, $exportEnd]);
        }
        $exportOrders = $exportOrdersQuery->get();

        $details = DetailPesanan::with(['pesanan', 'produk.kategori'])
            ->whereHas('pesanan', function ($query) use ($summaryStart, $summaryEnd, $isAllTime) {
                $query->when(!$isAllTime, fn ($orderQuery) => $orderQuery->whereBetween('tgl_pesanan', [$summaryStart, $summaryEnd]))
                    ->where('status_pesanan', 'selesai');
            })
            ->where('subtotal', '>', 0)
            ->get();
        $detailsWithProduct = $details->filter(fn ($detail) => $detail->produk !== null);
        $bestSellerDetails = DetailPesanan::with(['produk.kategori'])
            ->whereHas('pesanan', function ($query) use ($summaryStart, $summaryEnd, $isAllTime) {
                $query->when(!$isAllTime, fn ($orderQuery) => $orderQuery->whereBetween('tgl_pesanan', [$summaryStart, $summaryEnd]))
                    ->where('status_pesanan', 'selesai');
            })
            ->where('subtotal', '>', 0)
            ->get()
            ->filter(fn ($detail) => $detail->produk !== null);
        $totalPenjualan = $validOrders->sum('total_harga');
        $totalOrder = $validOrders->count();
        $ordersForTraffic = $orders->where('status_pesanan', '!=', 'dibatalkan');
        $previousTotalPenjualan = $previousValidOrders->sum('total_harga');
        $previousTotalOrder = $previousValidOrders->count();
        $averageOrder = $totalOrder > 0 ? $totalPenjualan / $totalOrder : 0;
        $previousAverageOrder = $previousTotalOrder > 0
            ? $previousTotalPenjualan / $previousTotalOrder
            : 0;

        return [
            'period' => $isAllTime ? 'all' : $period,
            'analytics_year' => $base->year,
            'log_date' => $base->toDateString(),
            'export_period' => $exportPeriod,
            'start' => $isAllTime ? null : $summaryStart->toDateTimeString(),
            'end' => $isAllTime ? null : $summaryEnd->toDateTimeString(),
            'log_start' => $isAllTime ? null : $logStart->toDateTimeString(),
            'log_end' => $isAllTime ? null : $logEnd->toDateTimeString(),
            'export_start' => $exportStart->toDateTimeString(),
            'export_end' => $exportEnd->toDateTimeString(),
            'total_penjualan' => $totalPenjualan,
            'total_order' => $totalOrder,
            'perubahan' => [
                'total_penjualan' => $this->percentageChange($totalPenjualan, $previousTotalPenjualan),
                'total_order' => $this->percentageChange($totalOrder, $previousTotalOrder),
                'rata_rata_order' => $this->percentageChange($averageOrder, $previousAverageOrder),
            ],
            'best_seller_menu' => $bestSellerDetails
                ->groupBy(fn ($item) => $item->produk->getKey())
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
                ->take(5)
                ->values(),
            'kategori_populer' => $detailsWithProduct
                ->groupBy(fn ($item) => $item->produk->kategori?->nama_kategori ?? 'Tanpa Kategori')
                ->map(fn ($items, $kategori) => [
                    'kategori' => $kategori,
                    'jumlah' => $items->sum('jumlah_item'),
                ])
                ->sortByDesc('jumlah')
                ->values(),
            'jam_sibuk' => $ordersForTraffic
                ->groupBy(fn ($order) => Carbon::parse($order->tgl_pesanan)->format('H:00'))
                ->map(fn ($items, $jam) => [
                    'jam' => $jam,
                    'total' => $items->count(),
                ])
                ->sortByDesc('total')
                ->values(),
            'transaksi' => $logOrders->values(),
            'export_transaksi' => $exportOrders->values(),
        ];
    }

    private function resolveRange(string $period, ?string $date): array
    {
        $base = $date ? Carbon::parse($date) : now();

        return match ($period) {
            'week' => [$base->copy()->startOfWeek(), $base->copy()->endOfWeek()],
            'month' => [$base->copy()->startOfMonth(), $base->copy()->endOfMonth()],
            'year' => [$base->copy()->startOfYear(), $base->copy()->endOfYear()],
            default => [$base->copy()->startOfDay(), $base->copy()->endOfDay()],
        };
    }

    private function resolvePreviousRange(string $period, Carbon $start, Carbon $end): array
    {
        return match ($period) {
            'week' => [$start->copy()->subWeek(), $end->copy()->subWeek()],
            'month' => [$start->copy()->subMonth(), $end->copy()->subMonth()],
            'year' => [$start->copy()->subYear(), $end->copy()->subYear()],
            default => [$start->copy()->subDay(), $end->copy()->subDay()],
        };
    }

    private function resolveExportRange(string $period, Carbon $base): array
    {
        return match ($period) {
            'week' => [$base->copy()->startOfWeek(), $base->copy()->endOfWeek()],
            'month' => [$base->copy()->startOfMonth(), $base->copy()->endOfMonth()],
            'year' => [$base->copy()->startOfYear(), $base->copy()->endOfYear()],
            default => [$base->copy()->startOfDay(), $base->copy()->endOfDay()],
        };
    }

    private function percentageChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0.0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
