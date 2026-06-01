<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\OrderService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentGatewayService $paymentGatewayService
    ) {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'meja_id' => 'required|integer|exists:mejas,id_meja',
            'reservasi_id' => 'nullable|integer|exists:reservasis,id_reservasi',
            'tipe_pesanan' => 'required|in:dine_in,takeaway,take_away',
            'metode_pembayaran' => 'nullable|in:cash,qris',
            'catatan_pesanan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|integer|exists:produks,id_produk',
            'items.*.jumlah_item' => 'required|integer|min:1',
            'items.*.opsi_varian' => 'nullable|string|max:100',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat.',
            'data' => $this->orderService->checkout($data),
        ], 201);
    }

    public function paymentConfig()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'qris_enabled' => true,
                'provider' => 'static_qris',
                'confirmation' => 'manual_admin',
                'message' => 'QRIS statis aktif. Pembayaran dikonfirmasi manual oleh kasir/admin.',
            ],
        ]);
    }

    public function paymentStatus(Pesanan $pesanan)
    {
        return response()->json([
            'success' => true,
            'data' => $pesanan->fresh(['meja', 'reservasi', 'detail_pesanans.produk']),
        ]);
    }

    public function notification(Request $request)
    {
        $this->paymentGatewayService->handleNotification($request->all());

        return response()->json([
            'success' => true,
            'message' => 'OK',
        ]);
    }

    public function xenditWebhook(Request $request)
    {
        $this->paymentGatewayService->handleXenditWebhook($request->all());

        return response()->json([
            'success' => true,
            'message' => 'OK',
        ]);
    }
}
