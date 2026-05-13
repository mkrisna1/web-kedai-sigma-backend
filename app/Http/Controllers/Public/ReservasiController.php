<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreReservasiRequest;
use App\Services\ReservasiService;

class ReservasiController extends Controller
{
    protected ReservasiService $reservasiService;

    public function __construct(ReservasiService $reservasiService)
    {
        $this->reservasiService = $reservasiService;
    }

    public function store(StoreReservasiRequest $request)
    {
        $reservasi = $this->reservasiService->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat, menunggu konfirmasi admin.',
            'data'    => $reservasi,
        ], 201);
    }
}