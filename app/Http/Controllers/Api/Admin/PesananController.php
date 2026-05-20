<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->orderService->getAdminOrders(),
        ]);
    }

    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        $data = $request->validate([
            'status_pesanan' => 'required|in:menunggu_konfirmasi,diproses,selesai,dibatalkan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => $this->orderService->updateStatus($pesanan, $data['status_pesanan']),
        ]);
    }

    public function updatePayment(Request $request, Pesanan $pesanan)
    {
        $data = $request->validate([
            'status_pembayaran' => 'required|in:belum_bayar,lunas',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diperbarui.',
            'data' => $this->orderService->updatePayment($pesanan, $data['status_pembayaran']),
        ]);
    }

    public function resolveStockIssue(Request $request, Pesanan $pesanan)
    {
        $data = $request->validate([
            'detail_id' => 'required|integer|exists:detail_pesanans,id_detail',
            'action' => 'required|in:remove,replace',
            'replacement_produk_id' => 'required_if:action,replace|nullable|integer|exists:produks,id_produk',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perubahan stok pesanan berhasil disimpan.',
            'data' => $this->orderService->resolveStockIssue(
                $pesanan,
                $data['detail_id'],
                $data['action'],
                $data['replacement_produk_id'] ?? null
            ),
        ]);
    }

    public function receipt(Pesanan $pesanan)
    {
        return response()->json([
            'success' => true,
            'data' => $pesanan->load(['meja', 'reservasi', 'detail_pesanans.produk']),
        ]);
    }
}
