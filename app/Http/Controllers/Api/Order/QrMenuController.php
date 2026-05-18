<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Models\Meja;
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
            'meja_id' => 'nullable|integer|exists:mejas,id_meja',
            'table' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $mejaId = $data['meja_id'] ?? null;

        if (!$mejaId) {
            $mejaId = $this->resolveMejaId($data['name'] ?? $data['table'] ?? null);
        }

        return response()->json([
            'success' => true,
            'data' => $this->orderService->getQrContext($mejaId),
        ]);
    }

    private function resolveMejaId(?string $label): ?int
    {
        if (!$label) {
            return null;
        }

        $normalizedLabel = trim($label);
        $number = preg_replace('/\D+/', '', $normalizedLabel);
        $candidates = array_values(array_unique(array_filter([
            $normalizedLabel,
            $number ? 'Meja ' . str_pad($number, 2, '0', STR_PAD_LEFT) : null,
            $number ? 'M-' . (int) $number : null,
        ])));

        return Meja::query()
            ->whereIn('nomor_meja', $candidates)
            ->value('id_meja');
    }
}
