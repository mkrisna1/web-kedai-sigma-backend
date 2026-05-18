<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Reservasi;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        protected TableAvailabilityService $tableAvailabilityService
    ) {
    }

    public function summary(
        string $period = 'day',
        ?string $date = null,
        ?int $trafficMonth = null,
        ?int $trafficYear = null
    ): array
    {
        $this->tableAvailabilityService->releaseStaleOccupiedTables();

        [$start, $end] = $this->resolveRange($period, $date);
        $trafficBase = Carbon::create(
            $trafficYear ?: now()->year,
            $trafficMonth ?: now()->month,
            1
        );
        $incomeOrders = Pesanan::query()
            ->whereBetween('tgl_pesanan', [$start, $end])
            ->where('status_pesanan', '!=', 'dibatalkan');
        [$previousStart, $previousEnd] = $this->resolvePreviousRange($period, $start, $end);
        $currentIncome = (clone $incomeOrders)->sum('total_harga');
        $previousIncome = Pesanan::query()
            ->whereBetween('tgl_pesanan', [$previousStart, $previousEnd])
            ->where('status_pesanan', '!=', 'dibatalkan')
            ->sum('total_harga');
        $activeTables = Meja::query()->where('status_meja', '!=', 'maintenance');
        $totalTables = (clone $activeTables)->count();
        $occupiedTables = (clone $activeTables)
            ->where('used_seats', '>', 0)
            ->count();
        $availableTables = max($totalTables - $occupiedTables, 0);

        return [
            'periode_pendapatan' => $period,
            'pendapatan_kedai' => $currentIncome,
            'pendapatan_persen' => $this->percentageChange($currentIncome, $previousIncome),
            'pendapatan_harian' => Pesanan::query()
                ->whereDate('tgl_pesanan', today())
                ->where('status_pesanan', '!=', 'dibatalkan')
                ->sum('total_harga'),
            'meja_terisi' => $occupiedTables,
            'meja_sisa' => $availableTables,
            'total_meja' => $totalTables,
            'reservasi_belum_diproses' => Reservasi::where('status_reservasi', 'menunggu_konfirmasi')->count(),
            'transaksi_terakhir' => Pesanan::with(['meja', 'detail_pesanans.produk'])
                ->latest('tgl_pesanan')
                ->limit(8)
                ->get(),
            'traffic_month' => $trafficBase->month,
            'traffic_year' => $trafficBase->year,
            'grafik_jam_ramai' => $this->monthlyTraffic($trafficBase),
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
            'month' => [$start->copy()->subMonthNoOverflow(), $end->copy()->subMonthNoOverflow()],
            'year' => [$start->copy()->subYear(), $end->copy()->subYear()],
            default => [$start->copy()->subDay(), $end->copy()->subDay()],
        };
    }

    private function percentageChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0.0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function monthlyTraffic(Carbon $base)
    {
        $start = $base->copy()->startOfMonth();
        $end = $base->copy()->endOfMonth();
        $ordersByDay = Pesanan::query()
            ->whereBetween('tgl_pesanan', [$start, $end])
            ->where('status_pesanan', '!=', 'dibatalkan')
            ->get()
            ->groupBy(fn ($order) => Carbon::parse($order->tgl_pesanan)->format('Y-m-d'));

        return collect(range(1, $base->daysInMonth))
            ->map(function (int $day) use ($base, $ordersByDay) {
                $date = $base->copy()->day($day);
                $dateKey = $date->format('Y-m-d');

                return [
                    'tanggal' => $dateKey,
                    'label' => $date->format('d M'),
                    'hari' => $day,
                    'total' => $ordersByDay->get($dateKey, collect())->count(),
                ];
            })
            ->values();
    }
}
