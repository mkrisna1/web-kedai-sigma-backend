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
                'qris_enabled' => $this->paymentGatewayService->isConfigured(),
                'provider' => 'midtrans_gopay',
                'message' => $this->paymentGatewayService->isConfigured()
                    ? 'QRIS siap digunakan.'
                    : 'QRIS belum aktif. Silakan pilih pembayaran tunai dulu.',
            ],
        ]);
    }

    public function paymentStatus(Pesanan $pesanan)
    {
        $pesanan = $this->paymentGatewayService->syncPaymentStatus($pesanan);

        return response()->json([
            'success' => true,
            'data' => $pesanan,
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
}
