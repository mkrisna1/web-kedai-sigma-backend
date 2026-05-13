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
        return response()->json([
            'success' => true,
            'message' => 'Status reservasi berhasil diperbarui.',
            'data' => $this->reservasiService->updateStatus(
                $reservasi,
                $request->validated()['status_reservasi'],
                $request->user()?->id,
                $request->validated()['meja_id'] ?? null
            ),
        ]);
    }
}
