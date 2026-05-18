<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Reservasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected TableAvailabilityService $tableAvailabilityService
    ) {
    }

    public function getQrContext(?int $mejaId = null): array
    {
        return [
            'meja' => $mejaId ? Meja::find($mejaId) : null,
            'reservasi_aktif' => $mejaId
                ? Reservasi::query()
                    ->where('id_meja', $mejaId)
                    ->whereDate('tgl_reservasi', today())
                    ->where('status_reservasi', 'dikonfirmasi')
                    ->latest('jam_reservasi')
                    ->first()
                : null,
            'menu' => Produk::with('kategori')
                ->orderBy('nama_produk')
                ->get(),
        ];
    }

    public function checkout(array $data): Pesanan
    {
        if (($data['tipe_pesanan'] ?? null) === 'take_away') {
            $data['tipe_pesanan'] = 'takeaway';
        }

        return DB::transaction(function () use ($data) {
            $meja = Meja::find($data['meja_id']);

            if (!$meja || $meja->status_meja !== 'active') {
                throw ValidationException::withMessages([
                    'meja_id' => 'Meja tidak tersedia.',
                ]);
            }

            $reservasiId = $data['reservasi_id'] ?? null;
            $hasReservationColumn = Schema::hasColumn('pesanans', 'id_reservasi');
            $hasPaymentStatusColumn = Schema::hasColumn('pesanans', 'status_pembayaran');
            $hasOrderTypeColumn = Schema::hasColumn('pesanans', 'tipe_pesanan');
            $hasItemVariantColumn = Schema::hasColumn('detail_pesanans', 'opsi_varian');
            $hasDetailTableIdColumn = Schema::hasColumn('detail_pesanans', 'id_meja');
            $hasDetailTableNumberColumn = Schema::hasColumn('detail_pesanans', 'nomor_meja');
            $items = collect($data['items']);
            $productIds = $items->pluck('produk_id')->unique()->values();
            $products = Produk::query()
                ->whereIn('id_produk', $productIds)
                ->where('ketersediaan_produk', 'tersedia')
                ->get()
                ->keyBy('id_produk');

            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Ada menu yang tidak tersedia.',
                ]);
            }

            $total = $items->reduce(function ($total, $item) use ($products) {
                $product = $products[$item['produk_id']];

                return $total + ((float) $product->harga_produk * (int) $item['jumlah_item']);
            }, 0);

            $orderPayload = [
                'id_meja' => $data['meja_id'],
                'tgl_pesanan' => now(),
                'status_pesanan' => 'menunggu_konfirmasi',
                'total_harga' => $total,
                'catatan_pesanan' => $data['catatan_pesanan'] ?? null,
            ];

            if ($reservasiId !== null && $hasReservationColumn) {
                $orderPayload['id_reservasi'] = $reservasiId;
            }

            if ($hasPaymentStatusColumn) {
                $orderPayload['status_pembayaran'] = 'belum_bayar';
            }

            if ($hasOrderTypeColumn) {
                $orderPayload['tipe_pesanan'] = $data['tipe_pesanan'];
            }

            $pesanan = Pesanan::create($orderPayload);

            foreach ($items as $item) {
                $product = $products[$item['produk_id']];

                $detailPayload = [
                    'id_produk' => $product->getKey(),
                    'jumlah_item' => (int) $item['jumlah_item'],
                    'subtotal' => (float) $product->harga_produk * (int) $item['jumlah_item'],
                ];

                if ($hasDetailTableIdColumn) {
                    $detailPayload['id_meja'] = $meja->getKey();
                }

                if ($hasDetailTableNumberColumn) {
                    $detailPayload['nomor_meja'] = $meja->nomor_meja;
                }

                if ($hasItemVariantColumn) {
                    $detailPayload['opsi_varian'] = $item['opsi_varian'] ?? null;
                }

                $pesanan->detail_pesanans()->create($detailPayload);
            }

            return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
        });
    }

    public function getAdminOrders()
    {
        $this->deleteExpiredCancelledOrders();
        $this->tableAvailabilityService->releaseStaleOccupiedTables();

        return Pesanan::with(['meja', 'reservasi', 'detail_pesanans.produk'])
            ->latest('tgl_pesanan')
            ->get();
    }

    public function updateStatus(Pesanan $pesanan, string $status): Pesanan
    {
        return DB::transaction(function () use ($pesanan, $status) {
            $payload = [
                'status_pesanan' => $status,
            ];

            if (Schema::hasColumn('pesanans', 'status_pembayaran')) {
                $payload['status_pembayaran'] = $status === 'selesai'
                    ? 'lunas'
                    : 'belum_bayar';
            }

            $pesanan->update($payload);

            if ($status === 'diproses') {
                $this->markTableOccupied($pesanan);
            }

            if ($status === 'dibatalkan') {
                $this->releaseTableWhenNoActiveOrders($pesanan);
            }

            return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
        });
    }

    public function updatePayment(Pesanan $pesanan, string $status): Pesanan
    {
        if (Schema::hasColumn('pesanans', 'status_pembayaran')) {
            $pesanan->update([
                'status_pembayaran' => $status,
            ]);
        }

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    public function resolveStockIssue(
        Pesanan $pesanan,
        int $detailId,
        string $action,
        ?int $replacementProductId = null
    ): Pesanan {
        return DB::transaction(function () use ($pesanan, $detailId, $action, $replacementProductId) {
            $pesanan->load('detail_pesanans.produk');
            $detail = $pesanan->detail_pesanans->firstWhere('id_detail', $detailId);

            if (! $detail) {
                throw ValidationException::withMessages([
                    'detail_id' => 'Item pesanan tidak ditemukan.',
                ]);
            }

            if ($action === 'remove') {
                $detail->update([
                    'subtotal' => 0,
                    'opsi_varian' => 'Stok habis',
                ]);
            }

            if ($action === 'replace') {
                $replacement = Produk::query()
                    ->where('id_produk', $replacementProductId)
                    ->where('ketersediaan_produk', 'tersedia')
                    ->first();

                if (! $replacement) {
                    throw ValidationException::withMessages([
                        'replacement_produk_id' => 'Menu pengganti tidak tersedia.',
                    ]);
                }

                $oldProductName = $detail->produk?->nama_produk ?? 'menu sebelumnya';
                $quantity = max((int) $detail->jumlah_item, 1);

                $detail->update([
                    'id_produk' => $replacement->getKey(),
                    'subtotal' => (float) $replacement->harga_produk * $quantity,
                    'opsi_varian' => "Pengganti {$oldProductName}",
                ]);
            }

            $total = (float) $pesanan->detail_pesanans()->sum('subtotal');
            $payload = ['total_harga' => $total];

            if ($total <= 0) {
                $payload['status_pesanan'] = 'dibatalkan';
            }

            $pesanan->update($payload);

            if (($payload['status_pesanan'] ?? null) === 'dibatalkan') {
                $this->releaseTableWhenNoActiveOrders($pesanan);
            }

            return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
        });
    }

    private function markTableOccupied(Pesanan $pesanan): void
    {
        if (! $pesanan->id_meja || ! Schema::hasColumn('mejas', 'used_seats')) {
            return;
        }

        Meja::query()
            ->whereKey($pesanan->id_meja)
            ->where('status_meja', 'active')
            ->update(['used_seats' => 1]);
    }

    private function deleteExpiredCancelledOrders(): void
    {
        if (
            ! Schema::hasColumn('pesanans', 'status_pesanan') ||
            ! Schema::hasColumn('pesanans', 'updated_at')
        ) {
            return;
        }

        Pesanan::query()
            ->where('status_pesanan', 'dibatalkan')
            ->where('updated_at', '<=', now()->subDay())
            ->delete();
    }

    private function releaseTableWhenNoActiveOrders(Pesanan $pesanan): void
    {
        if (! $pesanan->id_meja || ! Schema::hasColumn('mejas', 'used_seats')) {
            return;
        }

        $hasActiveOrder = Pesanan::query()
            ->where('id_meja', $pesanan->id_meja)
            ->where('status_pesanan', 'diproses')
            ->exists();

        if (! $hasActiveOrder) {
            Meja::query()
                ->whereKey($pesanan->id_meja)
                ->where('status_meja', 'active')
                ->update(['used_seats' => 0]);
        }
    }
}
