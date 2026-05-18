<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReservasiStatusRequest;
use App\Models\Reservasi;
use App\Services\ReservasiService;

class ReservasiController extends Controller
{
    public function __construct(
        protected ReservasiService $reservasiService
    ) {
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->reservasiService->getAllForAdmin(),
        ]);
    }

    public function updateStatus(
        UpdateReservasiStatusRequest $request,
        Reservasi $reservasi
    ) {
        $validated = $request->validated();

        return response()->json([
            'success' => true,
            'message' => 'Status reservasi berhasil diperbarui.',
            'data' => $this->reservasiService->updateStatus(
                $reservasi,
                $validated['status_reservasi'],
                $request->user()?->getKey(),
                $validated['meja_id'] ?? null
            ),
        ]);
    }

    public function destroy(Reservasi $reservasi)
    {
        $this->reservasiService->delete($reservasi);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dihapus.',
        ]);
    }
}
