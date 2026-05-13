<?php

namespace App\Http\Controllers\Admin;

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
        $reservasis = $this->reservasiService->getAllForAdmin();

        return response()->json([
            'success' => true,
            'data' => $reservasis,
        ]);
    }

    public function updateStatus(
        UpdateReservasiStatusRequest $request,
        Reservasi $reservasi
    ) {
        $updatedReservasi = $this->reservasiService->updateStatus(
            $reservasi,
            $request->validated()['status_reservasi'],
            $request->user()?->id,
            $request->validated()['meja_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Status reservasi berhasil diperbarui.',
            'data' => $updatedReservasi,
        ]);
    }
}
