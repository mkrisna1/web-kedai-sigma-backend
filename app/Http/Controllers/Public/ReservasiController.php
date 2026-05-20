<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreReservasiRequest;
use App\Services\ReservasiService;
use Illuminate\Http\Request;

class ReservasiController extends Controller
{
    protected ReservasiService $reservasiService;

    public function __construct(ReservasiService $reservasiService)
    {
        $this->reservasiService = $reservasiService;
    }

    public function tables(Request $request)
    {
        $guestCount = $request->integer('jml_orang') ?: null;
        $date = $request->input('tgl_reservasi');
        $time = $request->input('jam_reservasi');

        return response()->json([
            'success' => true,
            'data' => $this->reservasiService->getAvailableTables($guestCount, $date, $time),
        ]);
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
