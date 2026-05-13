<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Reservasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function getQrContext(?int $mejaId = null): array
    {
        return [
            'meja' => $mejaId ? Meja::find($mejaId) : null,
            'reservasi_aktif' => $mejaId
                ? Reservasi::query()
                    ->where('meja_id', $mejaId)
                    ->whereDate('tgl_reservasi', today())
                    ->where('status_reservasi', 'dikonfirmasi')
                    ->latest('jam_reservasi')
                    ->first()
                : null,
            'menu' => Produk::with('kategori')
                ->where('ketersediaan_produk', 'tersedia')
                ->orderBy('nama_produk')
                ->get(),
        ];
    }

    public function checkout(array $data): Pesanan
    {
        return DB::transaction(function () use ($data) {
            $meja = Meja::find($data['meja_id']);

            if (!$meja || $meja->status_meja !== 'active') {
                throw ValidationException::withMessages([
                    'meja_id' => 'Meja tidak tersedia.',
                ]);
            }

            $reservasiId = $data['reservasi_id'] ?? null;
            $items = collect($data['items']);
            $productIds = $items->pluck('produk_id')->unique()->values();
            $products = Produk::query()
                ->whereIn('id', $productIds)
                ->where('ketersediaan_produk', 'tersedia')
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Ada menu yang tidak tersedia.',
                ]);
            }

            $total = $items->reduce(function ($total, $item) use ($products) {
                $product = $products[$item['produk_id']];

                return $total + ((float) $product->harga_produk * (int) $item['jumlah_item']);
            }, 0);

            $pesanan = Pesanan::create([
                'meja_id' => $data['meja_id'],
                'reservasi_id' => $reservasiId,
                'tgl_pesanan' => now(),
                'status_pesanan' => 'menunggu_konfirmasi',
                'status_pembayaran' => 'belum_bayar',
                'tipe_pesanan' => $data['tipe_pesanan'] ?? 'dine_in',
                'total_harga' => $total,
                'catatan_pesanan' => $data['catatan_pesanan'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = $products[$item['produk_id']];

                $pesanan->detail_pesanans()->create([
                    'produk_id' => $product->id,
                    'jumlah_item' => (int) $item['jumlah_item'],
                    'opsi_varian' => $item['opsi_varian'] ?? null,
                    'subtotal' => (float) $product->harga_produk * (int) $item['jumlah_item'],
                ]);
            }

            return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
        });
    }

    public function getAdminOrders()
    {
        return Pesanan::with(['meja', 'reservasi', 'detail_pesanans.produk'])
            ->latest('tgl_pesanan')
            ->get();
    }

    public function updateStatus(Pesanan $pesanan, string $status): Pesanan
    {
        $pesanan->update([
            'status_pesanan' => $status,
        ]);

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }

    public function updatePayment(Pesanan $pesanan, string $status): Pesanan
    {
        $pesanan->update([
            'status_pembayaran' => $status,
        ]);

        return $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']);
    }
}
