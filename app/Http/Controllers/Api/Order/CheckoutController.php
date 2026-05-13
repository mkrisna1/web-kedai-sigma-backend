<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'meja_id' => 'required|integer|exists:mejas,id',
            'reservasi_id' => 'nullable|integer|exists:reservasis,id',
            'tipe_pesanan' => 'required|in:dine_in,takeaway',
            'catatan_pesanan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|integer|exists:produks,id',
            'items.*.jumlah_item' => 'required|integer|min:1',
            'items.*.opsi_varian' => 'nullable|string|max:100',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat. Silakan bayar di kasir.',
            'data' => $this->orderService->checkout($data),
        ], 201);
    }
}
