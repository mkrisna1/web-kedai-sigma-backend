<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class QrMenuController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'meja_id' => 'nullable|integer|exists:mejas,id',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->orderService->getQrContext($data['meja_id'] ?? null),
        ]);
    }
}
